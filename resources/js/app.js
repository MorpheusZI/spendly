
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';

import Chart from 'chart.js/auto';
import DataTable from 'datatables.net-bs5';

import tinymce from 'tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';

import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';

window.Chart = Chart;

document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('#transactions-table');

    if (table) {
        new DataTable(table, {
            responsive: false,
            scrollX: false,
            pageLength: 10,
            lengthMenu: [10, 15, 25, 50, 100],
            ordering: true,
            searching: true,
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari transaksi...',
                lengthMenu: 'Tampilkan _MENU_ transaksi',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ transaksi',
                infoEmpty: 'Tidak ada transaksi',
                infoFiltered: '(disaring dari _MAX_ transaksi)',
                zeroRecords: 'Transaksi tidak ditemukan',
                emptyTable: 'Belum ada transaksi',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya',
                },
                aria: {
                    sortAscending: ': aktifkan untuk mengurutkan ascending',
                    sortDescending: ': aktifkan untuk mengurutkan descending',
                },
            },
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false,
                },
            ],
        });
    }

    const editor = document.querySelector('#journal-editor');

    if (editor) {
        tinymce.init({
            target: editor,
            license_key: 'gpl',
            height: 450,
            menubar: false,
            plugins: 'lists link table wordcount',
            toolbar:
                'undo redo | blocks | bold italic underline | ' +
                'bullist numlist | link table | removeformat',
            branding: false,
            promotion: false,
            statusbar: true,
            skin: false,
            content_css: false,
            content_style: `
                body {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.6;
                    padding: 12px;
                }
            `,
            block_formats:
                'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3',
            setup(editorInstance) {
                editorInstance.on('change', () => {
                    editorInstance.save();
                });
            },
        });
    }
});

