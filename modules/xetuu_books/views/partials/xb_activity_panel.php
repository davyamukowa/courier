<?php
/**
 * Enterprise Activity Timeline Panel
 *
 * Expected variables:
 *   $activity_doc_type  string  e.g. 'move', 'budget', 'asset'
 *   $activity_doc_id    int     document ID (0 = new, no activity shown)
 *   $activity_base_url  string  base AJAX URL, e.g. admin_url('xetuu_books/ajax')
 */
defined('BASEPATH') or exit('No direct script access allowed');

$_doc_type = $activity_doc_type ?? '';
$_doc_id   = (int)($activity_doc_id ?? 0);
$_base_url = $activity_base_url ?? admin_url('xetuu_books/ajax');
?>

<div class="xb-collapsible-section" style="margin-bottom:0;">
    <div class="xb-section-header">
        <span><i class="fa fa-history"></i> Activity Timeline</span>
        <i class="fa fa-chevron-down xb-collapse-icon"></i>
    </div>
    <div class="xb-section-body">

        <?php if (!$_doc_id): ?>
        <p class="text-muted text-center" style="padding:10px;">Save this document to enable activity tracking.</p>
        <?php else: ?>

        <!-- Timeline container — populated by XBWorkspace.loadActivity() -->
        <div id="xb-activity-container"
             data-doc-type="<?php echo htmlspecialchars($_doc_type); ?>"
             data-doc-id="<?php echo $_doc_id; ?>"
             data-base-url="<?php echo $_base_url; ?>">
            <p class="text-muted text-center"><i class="fa fa-spinner fa-spin"></i> Loading…</p>
        </div>

        <!-- Comment form -->
        <div class="xb-comment-form">
            <form id="xb-comment-form">
                <div class="form-group" style="margin-bottom:8px;">
                    <textarea id="xb-comment-text" class="form-control"
                              rows="2" placeholder="Add a note or comment…"></textarea>
                </div>
                <button type="submit" class="btn btn-sm xb-btn-primary">
                    <i class="fa fa-comment"></i> Post Comment
                </button>
            </form>
        </div>

        <script>
        $(function () {
            var baseUrl  = '<?php echo addslashes($_base_url); ?>';
            var docType  = '<?php echo addslashes($_doc_type); ?>';
            var docId    = <?php echo $_doc_id; ?>;
            XBWorkspace.loadActivity(baseUrl, docType, docId, '#xb-activity-container');

            $('#xb-comment-form').on('submit', function (e) {
                e.preventDefault();
                var txt = $('#xb-comment-text').val().trim();
                if (!txt) return;
                var $btn = $(this).find('[type=submit]');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $.post(baseUrl + '/save_comment', { doc_type: docType, doc_id: docId, comment: txt }, function (res) {
                    try {
                        var r = typeof res === 'string' ? JSON.parse(res) : res;
                        if (r.success) {
                            $('#xb-comment-text').val('');
                            XBWorkspace.loadActivity(baseUrl, docType, docId, '#xb-activity-container');
                        }
                    } catch (ex) {}
                    $btn.prop('disabled', false).html('<i class="fa fa-comment"></i> Post Comment');
                });
            });
        });
        </script>

        <?php endif; ?>
    </div>
</div>
