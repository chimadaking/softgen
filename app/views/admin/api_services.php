<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
    $instance   = $data['instance'] ?? null;
    $filters    = $data['filters'] ?? [];
    $pagination = $data['pagination'] ?? [];

    $q = $filters['q'] ?? '';
    $perPage = (int)($filters['per_page'] ?? 50);

    $page = (int)($pagination['page'] ?? 1);
    $total = (int)($pagination['total'] ?? 0);
    $totalPages = (int)($pagination['total_pages'] ?? 1);

    if ($page < 1) $page = 1;
    if ($perPage < 10) $perPage = 10;
    if ($perPage > 200) $perPage = 200;
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) $page = $totalPages;

    // IMPORTANT: preserve router param when rewrite is OFF
    $routerUrl = $_GET['url'] ?? '';

    function base_self(): string {
        return $_SERVER['PHP_SELF'] ?? '';
    }

    function build_link(array $overrides = []): string {
        $qs = $_GET;

        foreach ($overrides as $k => $v) {
            if ($v === null || $v === '') {
                unset($qs[$k]);
            } else {
                $qs[$k] = $v;
            }
        }

        // Ensure we never lose the router param in rewrite OFF mode
        if (isset($_GET['url']) && $_GET['url'] !== '' && !isset($qs['url'])) {
            $qs['url'] = $_GET['url'];
        }

        $query = http_build_query($qs);
        $base  = base_self();

        return $query ? ($base . '?' . $query) : $base;
    }

    function reset_link(): string {
        $base = base_self();
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            return $base . '?url=' . urlencode($_GET['url']);
        }
        return $base;
    }
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-0">Manage API Services</h1>
            <?php if ($instance): ?>
                <small class="text-muted">Instance: <strong><?php echo h($instance->name); ?></strong></small>
            <?php endif; ?>
        </div>

        <a href="<?php echo site_url('api'); ?>" class="btn btn-secondary">
            ← Back to API Instances
        </a>
    </div>

    <?php flash('success'); ?>
    <?php flash('error', '', 'alert alert-danger'); ?>

    <?php if ($instance): ?>
        <!-- SEARCH + PER PAGE (auto-submit; Reset kept; Apply removed) -->
        <form method="get" action="<?php echo h(base_self()); ?>" class="row g-2 align-items-end mb-3" id="serviceFilterForm">
            <?php if (!empty($routerUrl)): ?>
                <input type="hidden" name="url" value="<?php echo h($routerUrl); ?>">
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">Search services</label>
                <input
                    type="text"
                    name="q"
                    value="<?php echo h($q); ?>"
                    class="form-control"
                    placeholder="Search by name, category, or external ID..."
                    id="serviceSearchInput"
                    autocomplete="off"
                >
            </div>

            <div class="col-md-2">
                <label class="form-label">Per page</label>
                <select name="per_page" class="form-select" id="perPageSelect">
                    <?php foreach ([10,25,50,100,200] as $n): ?>
                        <option value="<?php echo $n; ?>" <?php echo ($perPage === $n) ? 'selected' : ''; ?>>
                            <?php echo $n; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <input type="hidden" name="page" value="1" id="pageHidden">
                <a class="btn btn-outline-secondary" href="<?php echo h(reset_link()); ?>">Reset</a>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($data['services'])): ?>

        <!-- BULK ACTION BAR (separate form; does NOT replace search/pagination) -->
        <?php if ($instance): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <form method="post" action="<?php echo site_url('api/bulkUpdateServices'); ?>" id="bulkForm" class="row g-2 align-items-end">
                        <?php echo csrf_field(); ?>

                        <input type="hidden" name="instance_id" value="<?php echo (int)$instance->id; ?>">
                        <input type="hidden" name="ids_csv" id="bulk_ids_csv" value="">

                        <!-- preserve list state after apply -->
                        <input type="hidden" name="q" value="<?php echo h($q); ?>">
                        <input type="hidden" name="per_page" value="<?php echo (int)$perPage; ?>">
                        <input type="hidden" name="page" value="<?php echo (int)$page; ?>">

                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1">Bulk action</label>
                            <select name="bulk_action" id="bulk_action" class="form-select">
                                <option value="">— Select action —</option>
                                <option value="visible_yes">Set Visible: Yes</option>
                                <option value="visible_no">Set Visible: No</option>
                                <option value="status_active">Set Status: Active</option>
                                <option value="status_inactive">Set Status: Inactive</option>
                                <option value="set_markup">Set Markup (selected)</option>
                                <option value="publish_products">Publish Selected to Products</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3" id="bulk_markup_wrap" style="display:none;">
                            <label class="form-label mb-1">Markup</label>
                            <!-- SAME markup options you already use -->
                            <select name="bulk_markup" id="bulk_markup" class="form-select">
                                <option value="0">0%</option>
                                <option value="0.50">50%</option>
                                <option value="1.00">100%</option>
                                <option value="1.50">150%</option>
                                <option value="2.00">200%</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="form-text mb-1">Selected: <strong><span id="selectedCount">0</span></strong></div>
                            <button type="submit" class="btn btn-primary w-100" id="bulkApplyBtn" disabled>
                                Apply to Selected
                            </button>
                        </div>

                        <div class="col-12 col-md-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="bulkClearBtn">
                                Clear selection
                            </button>
                        </div>
                    </form>

                    <div class="form-text mt-2">
                        Bulk actions change only the selected services. Markup uses your existing logic (final_price is calculated in your backend).
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:44px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Category</th>
                        <th>API Rate</th>
                        <th>Markup</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Visible</th>
                        <th>Extras</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['services'] as $s): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input svc-check" value="<?php echo (int)$s->id; ?>">
                        </td>
                        <td><?php echo (int)$s->id; ?></td>
                        <td>
                            <strong><?php echo h($s->name); ?></strong><br>
                            <small class="text-muted">
                                <?php echo h($s->instance_name ?? ''); ?>
                            </small>
                        </td>
                        <td><?php echo h($s->category); ?></td>
                        <td>$<?php echo number_format((float)$s->api_rate, 4); ?></td>

                        <form method="post" action="<?php echo site_url('api/updateService'); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$s->id; ?>">

                            <td>
                                <select name="markup" class="form-select form-select-sm">
                                    <option value="0" <?php if ((float)$s->markup == 0) echo 'selected'; ?>>0%</option>
                                    <option value="0.50" <?php if ((float)$s->markup == 0.50) echo 'selected'; ?>>50%</option>
                                    <option value="1.00" <?php if ((float)$s->markup == 1.00) echo 'selected'; ?>>100%</option>
                                    <option value="1.50" <?php if ((float)$s->markup == 1.50) echo 'selected'; ?>>150%</option>
                                    <option value="2.00" <?php if ((float)$s->markup == 2.00) echo 'selected'; ?>>200%</option>
                                </select>
                            </td>

                            <td>
                                <strong>$<?php echo number_format((float)$s->final_price, 4); ?></strong>
                            </td>

                            <td>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="inactive" <?php if ($s->status==='inactive') echo 'selected'; ?>>Inactive</option>
                                    <option value="active" <?php if ($s->status==='active') echo 'selected'; ?>>Active</option>
                                </select>
                            </td>

                            <td>
                                <select name="visible" class="form-select form-select-sm">
                                    <option value="no" <?php if ($s->visible==='no') echo 'selected'; ?>>No</option>
                                    <option value="yes" <?php if ($s->visible==='yes') echo 'selected'; ?>>Yes</option>
                                </select>
                            </td>

                            <td>
                                <?php if (!empty($s->extra)): ?>
                                    <details>
                                        <summary>View</summary>
                                        <pre class="small bg-light p-2 mb-0"><?php echo h(json_encode(json_decode($s->extra), JSON_PRETTY_PRINT)); ?></pre>
                                    </details>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td>
                                <button class="btn btn-sm btn-success">
                                    Save
                                </button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION (kept) -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    <?php
                        $from = ($page - 1) * $perPage + 1;
                        $to   = min($total, $page * $perPage);
                        echo "Showing {$from}-{$to} of {$total}";
                    ?>
                </div>

                <nav aria-label="Service pagination">
                    <ul class="pagination mb-0">
                        <?php
                            $prev = max(1, $page - 1);
                            $next = min($totalPages, $page + 1);
                        ?>

                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo h(build_link(['page' => $prev])); ?>">Prev</a>
                        </li>

                        <?php
                            $start = max(1, $page - 3);
                            $end   = min($totalPages, $page + 3);

                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . h(build_link(['page' => 1])) . '">1</a></li>';
                                if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                            }

                            for ($p = $start; $p <= $end; $p++) {
                                $active = ($p === $page) ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . h(build_link(['page' => $p])) . '">' . $p . '</a></li>';
                            }

                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                                echo '<li class="page-item"><a class="page-link" href="' . h(build_link(['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
                            }
                        ?>

                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo h(build_link(['page' => $next])); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center">
            <?php if (!empty($q)): ?>
                No services match your search.
            <?php else: ?>
                No services found. Sync an API instance first.
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script>
(function () {
    // ---- LIVE SEARCH + PER PAGE AUTO SUBMIT ----
    const filterForm = document.getElementById('serviceFilterForm');
    const input = document.getElementById('serviceSearchInput');
    const perPage = document.getElementById('perPageSelect');
    const pageHidden = document.getElementById('pageHidden');

    if (filterForm && input && perPage && pageHidden) {
        let t = null;

        function triggerFilter() {
            pageHidden.value = '1';
            filterForm.submit();
        }

        input.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(triggerFilter, 350);
        });

        perPage.addEventListener('change', function () {
            triggerFilter();
        });
    }

    // ---- BULK SELECTOR LOGIC ----
    const selectAll = document.getElementById('selectAll');
    const bulkForm = document.getElementById('bulkForm');
    const idsField = document.getElementById('bulk_ids_csv');
    const countEl = document.getElementById('selectedCount');
    const applyBtn = document.getElementById('bulkApplyBtn');
    const clearBtn = document.getElementById('bulkClearBtn');
    const actionSel = document.getElementById('bulk_action');
    const markupWrap = document.getElementById('bulk_markup_wrap');

    function checks() {
        return Array.from(document.querySelectorAll('.svc-check'));
    }

    function updateCount() {
        if (!countEl || !applyBtn) return;

        const selected = checks().filter(c => c.checked);
        countEl.textContent = String(selected.length);
        applyBtn.disabled = selected.length === 0 || (actionSel && actionSel.value === '');

        if (selectAll) {
            const all = checks();
            selectAll.checked = all.length > 0 && selected.length === all.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < all.length;
        }
    }

    function updateMarkupVisibility() {
        if (!actionSel || !markupWrap) return;
        markupWrap.style.display = (actionSel.value === 'set_markup') ? '' : 'none';
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks().forEach(c => c.checked = selectAll.checked);
            updateCount();
        });
    }

    checks().forEach(c => c.addEventListener('change', updateCount));

    if (actionSel) {
        actionSel.addEventListener('change', function () {
            updateMarkupVisibility();
            updateCount();
        });
        updateMarkupVisibility();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            checks().forEach(c => c.checked = false);
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateCount();
        });
    }

    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            const selectedIds = checks().filter(c => c.checked).map(c => c.value);

            if (selectedIds.length === 0) {
                e.preventDefault();
                alert('Please select at least one service.');
                return;
            }

            if (actionSel && actionSel.value === '') {
                e.preventDefault();
                alert('Please choose a bulk action.');
                return;
            }

            if (idsField) {
                idsField.value = selectedIds.join(',');
            }
        });
    }

    updateCount();
})();
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
