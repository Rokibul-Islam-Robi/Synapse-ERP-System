<?php
/**
 * Corporate Pagination Component Helper
 */

function paginate_data($pdo, $baseSql, $params = [], $perPage = 15, $pageParam = 'page') {
    $currentPage = max(1, (int)($_GET[$pageParam] ?? 1));
    $perPage = max(5, min(100, (int)($_GET['per_page'] ?? $perPage)));

    // Count total rows
    $countSql = "SELECT COUNT(*) FROM ({$baseSql}) AS count_subquery";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $totalRecords = (int)$stmtCount->fetchColumn();

    $totalPages = max(1, ceil($totalRecords / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    // Fetch paginated slice
    $paginatedSql = $baseSql . " LIMIT {$perPage} OFFSET {$offset}";
    $stmtData = $pdo->prepare($paginatedSql);
    $stmtData->execute($params);
    $data = $stmtData->fetchAll();

    return [
        'data' => $data,
        'total' => $totalRecords,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

function render_pagination($pagination, $pageParam = 'page') {
    if ($pagination['total_pages'] <= 1) {
        return "<div style='padding: 12px 20px; font-size: 12.5px; color: var(--text-muted); display:flex; justify-content:space-between; align-items:center;'>
            <span>Showing all <strong>{$pagination['total']}</strong> items</span>
        </div>";
    }

    $curr = $pagination['current_page'];
    $total = $pagination['total_pages'];
    $from = $pagination['offset'] + 1;
    $to = min($pagination['total'], $pagination['offset'] + $pagination['per_page']);

    // Build query string preserving existing GET params
    $queryParams = $_GET;

    $buildUrl = function($pageNum) use ($queryParams, $pageParam) {
        $queryParams[$pageParam] = $pageNum;
        return '?' . http_build_query($queryParams);
    };

    $html = "<div class='pagination-wrapper no-print'>
        <div class='pagination-info'>
            Showing <strong>{$from}</strong> to <strong>{$to}</strong> of <strong>{$pagination['total']}</strong> records
        </div>
        <div class='pagination-controls'>";

    // Previous button
    if ($curr > 1) {
        $html .= "<a href='" . htmlspecialchars($buildUrl($curr - 1)) . "' class='page-btn'>&laquo; Prev</a>";
    } else {
        $html .= "<span class='page-btn disabled'>&laquo; Prev</span>";
    }

    // Page numbers (smart window)
    $start = max(1, $curr - 2);
    $end = min($total, $curr + 2);

    if ($start > 1) {
        $html .= "<a href='" . htmlspecialchars($buildUrl(1)) . "' class='page-btn'>1</a>";
        if ($start > 2) $html .= "<span class='page-ellipsis'>...</span>";
    }

    for ($p = $start; $p <= $end; $p++) {
        if ($p === $curr) {
            $html .= "<span class='page-btn active'>{$p}</span>";
        } else {
            $html .= "<a href='" . htmlspecialchars($buildUrl($p)) . "' class='page-btn'>{$p}</a>";
        }
    }

    if ($end < $total) {
        if ($end < $total - 1) $html .= "<span class='page-ellipsis'>...</span>";
        $html .= "<a href='" . htmlspecialchars($buildUrl($total)) . "' class='page-btn'>{$total}</a>";
    }

    // Next button
    if ($curr < $total) {
        $html .= "<a href='" . htmlspecialchars($buildUrl($curr + 1)) . "' class='page-btn'>Next &raquo;</a>";
    } else {
        $html .= "<span class='page-btn disabled'>Next &raquo;</span>";
    }

    $html .= "</div></div>";
    return $html;
}
?>
