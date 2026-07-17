/**
 * Xetuu Books — Enterprise Transaction Workspace
 * Provides: auto-save, unsaved-changes guard, collapsible sections,
 * activity timeline, attachment drag-drop.
 */
(function ($) {
    'use strict';

    /* ── Status badge map ─────────────────────────────────────────────────── */
    var STATUS_MAP = {
        draft:      { cls: 'xb-status-draft',      label: 'Draft' },
        submitted:  { cls: 'xb-status-submitted',  label: 'Submitted' },
        approved:   { cls: 'xb-status-approved',   label: 'Approved' },
        posted:     { cls: 'xb-status-posted',      label: 'Posted' },
        cancelled:  { cls: 'xb-status-cancelled',  label: 'Cancelled' },
        cancel:     { cls: 'xb-status-cancelled',  label: 'Cancelled' },
        paid:       { cls: 'xb-status-paid',        label: 'Paid' },
        partial:    { cls: 'xb-status-partial',     label: 'Partially Paid' },
        overdue:    { cls: 'xb-status-overdue',     label: 'Overdue' },
        reconciled: { cls: 'xb-status-reconciled', label: 'Reconciled' },
        not_paid:   { cls: 'xb-status-not_paid',   label: 'Unpaid' },
        in_payment: { cls: 'xb-status-in_payment', label: 'In Payment' },
        confirmed:  { cls: 'xb-status-confirmed',  label: 'Confirmed' },
        open:       { cls: 'xb-status-open',        label: 'Open' },
        closed:     { cls: 'xb-status-closed',      label: 'Closed' },
    };

    /* ── Auto-save ────────────────────────────────────────────────────────── */
    var _autoSaveTimer    = null;
    var _hasUnsaved       = false;
    var _autoSaveUrl      = null;
    var _autoSaveFormId   = null;
    var _lastSavedTime    = null;
    var _autoSaveEnabled  = false;

    function _initAutoSave(opts) {
        _autoSaveUrl    = opts.url;
        _autoSaveFormId = opts.formId || '#xb-main-form';
        var interval    = opts.interval || 30000;
        _autoSaveEnabled = true;

        // Track any field change
        $(document).on('change input keyup', _autoSaveFormId + ' input, ' +
                                              _autoSaveFormId + ' select, ' +
                                              _autoSaveFormId + ' textarea', function () {
            _hasUnsaved = true;
            _showIndicator('unsaved');
        });

        // Unsaved-changes guard
        window.addEventListener('beforeunload', function (e) {
            if (_hasUnsaved) {
                var msg = 'You have unsaved changes. Leave anyway?';
                e.returnValue = msg;
                return msg;
            }
        });

        // Schedule auto-save
        _autoSaveTimer = setInterval(_doAutoSave, interval);

        // Draft recovery check
        _checkDraftRecovery(opts.docId, opts.docType);
    }

    function _doAutoSave() {
        if (!_hasUnsaved || !_autoSaveUrl) return;
        var $form = $(_autoSaveFormId);
        if (!$form.length) return;

        var data = $form.serializeArray();
        data.push({ name: '_autosave', value: '1' });

        $.ajax({
            url: _autoSaveUrl,
            method: 'POST',
            data: $.param(data),
            success: function (res) {
                try {
                    var r = typeof res === 'string' ? JSON.parse(res) : res;
                    if (r && r.success !== false) {
                        _hasUnsaved = false;
                        _lastSavedTime = new Date();
                        _showIndicator('saved');
                    } else {
                        _showIndicator('error');
                    }
                } catch (ex) {
                    // If controller returns a redirect HTML, treat as success
                    _hasUnsaved = false;
                    _lastSavedTime = new Date();
                    _showIndicator('saved');
                }
            },
            error: function () { _showIndicator('error'); }
        });
    }

    function _showIndicator(state) {
        var $ind = $('#xb-save-indicator');
        if (!$ind.length) return;
        clearTimeout($ind.data('hideTimer'));
        if (state === 'saved') {
            var t = _lastSavedTime ? (_lastSavedTime.getHours() + ':' +
                ('0' + _lastSavedTime.getMinutes()).slice(-2)) : '';
            $ind.html('<i class="fa fa-check" style="color:#16a34a;"></i> Saved ' + t).show();
            $ind.data('hideTimer', setTimeout(function () { $ind.fadeOut(400); }, 3500));
        } else if (state === 'unsaved') {
            $ind.html('<i class="fa fa-circle" style="color:#d97706;font-size:8px;vertical-align:middle;"></i> Unsaved').show();
        } else {
            $ind.html('<i class="fa fa-exclamation-circle" style="color:#dc2626;"></i> Auto-save failed').show();
        }
    }

    function _checkDraftRecovery(docId, docType) {
        if (!docId || !docType) return;
        var key = 'xb_draft_' + docType + '_' + docId;
        var saved = localStorage.getItem(key);
        if (saved) {
            // Optionally notify user — for now just clean up old drafts quietly
        }
    }

    /* ── Collapsible sections ─────────────────────────────────────────────── */
    function _initCollapsible() {
        $(document).on('click', '.xb-section-header', function (e) {
            if ($(e.target).is('button, a, input, select')) return;
            var $body = $(this).next('.xb-section-body');
            var $icon = $(this).find('.xb-collapse-icon');
            $body.slideToggle(180, function () {
                $icon.toggleClass('fa-chevron-down fa-chevron-up');
            });
        });
    }

    /* ── Activity timeline ────────────────────────────────────────────────── */
    function _loadActivity(baseUrl, docType, docId, containerId) {
        var $c = $(containerId);
        $c.html('<p class="text-muted text-center" style="padding:20px;"><i class="fa fa-spinner fa-spin"></i> Loading…</p>');

        $.get(baseUrl + '/get_activity', { doc_type: docType, doc_id: docId }, function (data) {
            var activities = [];
            try { activities = typeof data === 'string' ? JSON.parse(data) : data; } catch (ex) {}
            _renderTimeline(activities, containerId);
        }).fail(function () {
            $c.html('<p class="text-muted text-center" style="padding:20px;">Could not load activity.</p>');
        });
    }

    function _renderTimeline(items, containerId) {
        var $c = $(containerId);
        if (!Array.isArray(items) || !items.length) {
            $c.html('<p class="text-muted text-center" style="padding:20px;"><i class="fa fa-history"></i> No activity recorded yet.</p>');
            return;
        }
        var html = '<div class="xb-timeline">';
        items.forEach(function (a) {
            var dotClass = a.action === 'comment' ? 'comment' : (a.action === 'system' ? 'system' : '');
            html += '<div class="xb-timeline-item">' +
                '<div class="xb-timeline-dot ' + dotClass + '"></div>' +
                '<div class="xb-timeline-content">' +
                '<div class="xb-timeline-text">' + _esc(a.description || a.action) + '</div>' +
                '<div class="xb-timeline-meta">' +
                    '<strong>' + _esc(a.user_name || 'System') + '</strong>' +
                    (a.created_at ? ' &bull; ' + _esc(a.created_at) : '') +
                '</div>' +
                (a.comment ? '<div class="xb-timeline-comment-body">' + _esc(a.comment) + '</div>' : '') +
                '</div></div>';
        });
        html += '</div>';
        $c.html(html);
    }

    function _initCommentForm(baseUrl, docType, docId, containerId) {
        $(document).on('submit', '#xb-comment-form', function (e) {
            e.preventDefault();
            var txt = $('#xb-comment-text').val().trim();
            if (!txt) return;
            var $btn = $(this).find('[type=submit]');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.post(baseUrl + '/save_comment', {
                doc_type: docType,
                doc_id:   docId,
                comment:  txt
            }, function (res) {
                try {
                    var r = typeof res === 'string' ? JSON.parse(res) : res;
                    if (r.success) {
                        $('#xb-comment-text').val('');
                        _loadActivity(baseUrl, docType, docId, containerId);
                    }
                } catch (ex) {}
                $btn.prop('disabled', false).html('Post Comment');
            });
        });
    }

    /* ── Attachment drag-drop ─────────────────────────────────────────────── */
    function _initAttachments(baseUrl, docType, docId) {
        var $zone  = $('#xb-drop-zone');
        var $input = $('#xb-file-input');
        if (!$zone.length) return;

        $zone.on('click', function () { $input.click(); });

        $zone.on('dragover', function (e) {
            e.preventDefault(); e.stopPropagation();
            $(this).addClass('xb-dz-active');
        }).on('dragleave drop', function (e) {
            e.preventDefault(); e.stopPropagation();
            $(this).removeClass('xb-dz-active');
            if (e.type === 'drop') {
                _uploadFiles(e.originalEvent.dataTransfer.files, baseUrl, docType, docId);
            }
        });

        $input.on('change', function () {
            _uploadFiles(this.files, baseUrl, docType, docId);
            this.value = '';
        });

        _loadAttachments(baseUrl, docType, docId);
    }

    function _uploadFiles(files, baseUrl, docType, docId) {
        if (!files || !files.length) return;
        var $list = $('#xb-attachments-list');
        $list.prepend('<p id="xb-upload-progress" class="text-muted"><i class="fa fa-spinner fa-spin"></i> Uploading…</p>');

        Array.from(files).forEach(function (file) {
            var fd = new FormData();
            fd.append('file',     file);
            fd.append('doc_type', docType);
            fd.append('doc_id',   docId);

            $.ajax({
                url: baseUrl + '/upload_attachment',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    try {
                        var r = typeof res === 'string' ? JSON.parse(res) : res;
                        if (r.success) {
                            _loadAttachments(baseUrl, docType, docId);
                        } else {
                            alert(r.message || 'Upload failed.');
                        }
                    } catch (ex) {}
                    $('#xb-upload-progress').remove();
                },
                error: function () {
                    $('#xb-upload-progress').remove();
                    alert('Upload error. Please try again.');
                }
            });
        });
    }

    function _loadAttachments(baseUrl, docType, docId) {
        $.get(baseUrl + '/get_attachments', { doc_type: docType, doc_id: docId }, function (data) {
            var items = [];
            try { items = typeof data === 'string' ? JSON.parse(data) : data; } catch (ex) {}
            _renderAttachments(items);
        });
    }

    function _renderAttachments(items) {
        var $list = $('#xb-attachments-list');
        if (!Array.isArray(items) || !items.length) {
            $list.html('<p class="text-muted text-center" style="padding:12px;">No attachments yet.</p>');
            return;
        }
        var iconMap = { pdf: 'fa-file-pdf-o', doc: 'fa-file-word-o', docx: 'fa-file-word-o',
                        xls: 'fa-file-excel-o', xlsx: 'fa-file-excel-o',
                        png: 'fa-file-image-o', jpg: 'fa-file-image-o', jpeg: 'fa-file-image-o',
                        xml: 'fa-file-code-o' };
        var html = '';
        items.forEach(function (a) {
            var ext  = (a.original_name || '').split('.').pop().toLowerCase();
            var icon = iconMap[ext] || 'fa-file-o';
            var size = a.file_size > 1048576
                ? (a.file_size / 1048576).toFixed(1) + ' MB'
                : (a.file_size / 1024).toFixed(0) + ' KB';
            html += '<div class="xb-attachment-item">' +
                '<i class="fa ' + icon + ' xb-attachment-icon"></i>' +
                '<span class="xb-attachment-name">' + _esc(a.original_name) + '</span>' +
                '<span class="xb-attachment-meta">' + size + '</span>' +
                '<span class="xb-attachment-actions">' +
                    '<a href="' + _esc(a.download_url || '#') + '" title="Download" target="_blank"><i class="fa fa-download"></i></a>' +
                '</span>' +
                '</div>';
        });
        $list.html(html);
    }

    /* ── Helpers ──────────────────────────────────────────────────────────── */
    function _esc(str) { return $('<div/>').text(String(str || '')).html(); }

    function _statusBadge(state) {
        var s = STATUS_MAP[state] || { cls: 'xb-status-draft', label: state };
        return '<span class="xb-status-badge ' + s.cls + '">' + s.label + '</span>';
    }

    /* ── DOM ready ────────────────────────────────────────────────────────── */
    $(function () {
        _initCollapsible();

        // Inline tab change → load activity on first visit
        $('a[href="#activity"]').one('shown.bs.tab', function () {
            var $panel = $('#xb-activity-container');
            if (!$panel.length || $panel.data('loaded')) return;
            var docType = $panel.data('doc-type');
            var docId   = $panel.data('doc-id');
            var baseUrl = $panel.data('base-url');
            if (docType && docId && baseUrl) {
                _loadActivity(baseUrl, docType, docId, '#xb-activity-container');
                _initCommentForm(baseUrl, docType, docId, '#xb-activity-container');
                $panel.data('loaded', true);
            }
        });

        // Attachments tab
        $('a[href="#attachments"]').one('shown.bs.tab', function () {
            var $panel = $('#xb-attachments-panel');
            if (!$panel.length || $panel.data('loaded')) return;
            var docType = $panel.data('doc-type');
            var docId   = $panel.data('doc-id');
            var baseUrl = $panel.data('base-url');
            if (docType && docId && baseUrl) {
                _initAttachments(baseUrl, docType, docId);
                $panel.data('loaded', true);
            }
        });
    });

    /* ── Public API ───────────────────────────────────────────────────────── */
    window.XBWorkspace = {
        initAutoSave:    _initAutoSave,
        statusBadge:     _statusBadge,
        loadActivity:    _loadActivity,
        initAttachments: _initAttachments,
        markSaved:       function () { _hasUnsaved = false; },
        forceAutoSave:   _doAutoSave,
    };

}(jQuery));
