import naja from 'naja';
import netteForms from 'nette-forms';
import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle';
import '../js/dashboard.js';
import '../js/datepicker.min';
import 'summernote/dist/summernote-lite';
import Dropzone from 'dropzone';
import 'select2';


import 'bootstrap/dist/css/bootstrap.min.css';

require('bootstrap-icons/font/bootstrap-icons.css');
import 'sweetalert2/dist/sweetalert2.css';
import 'summernote/dist/summernote-lite.min.css';
import '../css/dropzone.scss';
import '../css/main.scss';
import 'select2/dist/css/select2.min.css';
global.jQuery = global.$ = require('jquery');

document.addEventListener('DOMContentLoaded', () => naja.initialize());

window.Nette = netteForms;
netteForms.initOnLoad();

Nette.validateControl = function (elem, rules, onlyCheck, value, emptyOptional) {
    return true;
}

Nette.validateForm = function (sender, onlyCheck) {
    return true;
}




// SWAL confirm
let swalConfirm = () => {
    $(document).on('click', '[data-confirm]', function () {
        var object = this;
        Swal.fire({
            title: $(object).data('title'),
            text: $(object).data('text'),
            icon: $(object).data('type'),
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Continue!'
        }).then((result) => {
            if (result.isConfirmed) {
                naja.makeRequest('POST', [$(this).data('url')]);
            }
        })
    });
}
naja.addEventListener('start', swalConfirm());


naja.uiHandler.addEventListener('interaction', (event) => {
    const {element} = event.detail;
    const question = element.dataset.confirm;
    if (question) {
        event.preventDefault();
    }
});



$(document).ready(function () {

    var el = document.getElementById('sortable');
    if ($(el).length > 0) {
        var sort = new Sortable(el, {
            draggable: '.item',
            handle: '.handle',
            animation: 150,
            sort: true,
            swapThreshold: 1,
            dataIdAttr: 'data-id',
            onEnd: function (evt) {
                // console.log($(el));
                let order = {};
                $(el).find('.item').each(function () {

                    order[$(this).data('id')] = $(this).index();
                });
                naja.makeRequest('POST', $(el).data('url'), {
                    repository: $(el).data('repository'),
                    position: order
                }, {forceRedirect: false});
            }
        });
    }

    $('.wysiwyg').summernote({
        height: 500,

    });

    //DATEPIKCER
    function initDatePicker() {
        //DATEPICKER
        $('.datepicker').datepicker({
            format: 'dd.mm.yyyy',
            autoHide: true,
            weekStart: 1,
        });
    }

    initDatePicker();

    $('.select2').select2({
        placeholder: 'Vyberte z ponuky',
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })


});

// Dropzone class
function initDropzone() {

    //Dropzone Configuration


    Dropzone.autoDiscover = false;

    var dropzone = new Dropzone("div#dropzone", {
        url: $('div#dropzone').data('url'),
        acceptedFiles: ".jpeg,.jpg,.png,.gif",
        maxFilesize: 10,
        timeout: 0,
        clickable: true
    });

    dropzone.on('error', function (file, response) {
        $(file.previewElement).find('.dz-error-message').css('opacity', 1).text(response);
    });

    dropzone.on('success', function (file, response) {
        if (typeof response.snippets !== 'undefined') {
            naja.snippetHandler.updateSnippets(response.snippets);
            initSortable();
        }

        if (typeof response.redirect !== 'undefined') {
            naja.redirectHandler.makeRedirect(response.redirect, true);
        }
    });

}

if ($("#dropzone").length > 0) {
    initDropzone();
}

function initSortable() {
    var sortable = new Sortable(sort, {
        handle: '.sort-handle',
        swapThreshold: 1,
        animation: 150,
        onSort: function (evt) {
            var items = JSON.stringify(sortable.toArray());
            naja.makeRequest('POST', $('#sort').data('url'), {'order': items}, {forceRedirect: true});
        },
    });
}

if ($("#sort").length > 0) {
    initSortable();
}
naja.addEventListener('complete', function () {
    if ($("#sort").length > 0) {
        initSortable();
    }
});




