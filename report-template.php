<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f2f5; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #0d9488 0%, #115e59 100%); color: white; padding: 24px 32px; }
        .header h1 { font-size: 24px; font-weight: 600; }
        .header .meta { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .header .back-link { color: white; text-decoration: none; font-size: 14px; opacity: 0.9; }
        .header .back-link:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }

        .filters { background: white; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .filters label { font-size: 13px; color: #666; font-weight: 500; }
        .filters select, .filters input { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; background: white; }
        .filters button { padding: 6px 16px; background: #0d9488; color: white; border: none; border-radius: 4px; font-size: 13px; cursor: pointer; }
        .filters button:hover { background: #0f766e; }
        .filters button.secondary { background: #f5f5f5; color: #333; border: 1px solid #ddd; }

        /* KPI 卡片 */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .kpi-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .kpi-card .label { font-size: 13px; color: #666; margin-bottom: 4px; }
        .kpi-card .value { font-size: 28px; font-weight: 700; }
        .kpi-card .sub { font-size: 12px; color: #999; margin-top: 2px; }
        .kpi-card.green .value { color: #0d9488; }
        .kpi-card.blue .value { color: #1a73e8; }
        .kpi-card.orange .value { color: #ea580c; }
        .kpi-card.purple .value { color: #7c3aed; }
        .kpi-card.red .value { color: #dc2626; }
        .kpi-card.teal .value { color: #0d9488; }

        /* 图表网格 */
        .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .chart-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .chart-card h3 { font-size: 15px; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f0; }
        .chart-card.full-width { grid-column: 1 / -1; }

        /* 表格 */
        .table-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table-card h3 { font-size: 15px; color: #333; margin-bottom: 16px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f8f9fa; padding: 10px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #e0e0e0; white-space: nowrap; cursor: pointer; }
        th:hover { background: #eef1f5; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 500; }
        .badge-new { background: #e3f2fd; color: #1565c0; }
        .badge-replied { background: #e8f5e9; color: #2e7d32; }
        .badge-quoting { background: #fff3e0; color: #e65100; }
        .badge-quoted { background: #f3e5f5; color: #7b1fa2; }
        .badge-sample { background: #e0f2f1; color: #00695c; }
        .badge-negotiating { background: #fce4ec; color: #c62828; }
        .badge-ordered { background: #e8eaf6; color: #283593; }
        .badge-closed { background: #e8f5e9; color: #1b5e20; }
        .badge-lost { background: #fafafa; color: #757575; }

        /* 排名条 */
        .rank-bar { display: flex; align-items: center; gap: 8px; }
        .rank-bar .bar { flex: 1; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden; }
        .rank-bar .bar-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
        .rank-bar .count { font-size: 13px; color: #333; min-width: 60px; text-align: right; }

        /* 转化漏斗 */
        .funnel { display: flex; flex-direction: column; gap: 8px; padding: 20px; }
        .funnel-stage { display: flex; align-items: center; gap: 12px; }
        .funnel-bar { height: 36px; border-radius: 4px; display: flex; align-items: center; padding: 0 12px; color: white; font-size: 13px; font-weight: 500; transition: width 0.5s; min-width: 60px; }
        .funnel-label { font-size: 13px; color: #555; min-width: 80px; }
        .funnel-count { font-size: 13px; color: #333; min-width: 40px; text-align: right; }
        .funnel-pct { font-size: 12px; color: #999; }

        /* 分页 */
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
        .pagination .info { font-size: 13px; color: #666; }
        .pagination .pages { display: flex; gap: 4px; }
        .pagination button { padding: 4px 10px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .pagination button:hover { background: #f5f5f5; }
        .pagination button.active { background: #0d9488; color: white; border-color: #0d9488; }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }

        .export-bar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 16px; }
        .export-bar button { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 4px; }
        .export-bar button:hover { background: #f5f5f5; }

        @media print {
            .filters, .export-bar, .pagination button { display: none !important; }
            .header { background: #0d9488 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: white; }
            .chart-card, .kpi-card, .table-card { box-shadow: none; border: 1px solid #eee; }
        }
        @media (max-width: 768px) {
            .charts-grid { grid-template-columns: 1fr; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h1>📊 <?= htmlspecialchars($title) ?></h1>
                <div class="meta">生成时间: <?= $generatedAt ?> | 询盘数: <?= InquiryReportGenerator::formatNumber($summary['total_inquiries']) ?> | 总预估金额: <?= InquiryReportGenerator::formatMoney($summary['total_estimated_value']) ?></div>
            </div>
            <a href="index.php" class="back-link">← 返回上传页</a>
        </div>
    </div>

    <div class="container">
        <!-- 筛选栏 -->
        <div class="filters">
            <label>国家:</label>
            <select id="filterCountry" onchange="applyFilters()">
                <option value="">全部</option>
                <?php arsort($summary['countries']); foreach ($summary['countries'] as $c => $n): ?>
                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?> (<?= $n ?>)</option>
                <?php endforeach; ?>
            </select>
            <label>来源:</label>
            <select id="filterSource" onchange="applyFilters()">
                <option value="">全部</option>
                <?php foreach (array_keys($summary['source_types']) as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
            </select>
            <label>产品:</label>
            <select id="filterProduct" onchange="applyFilters()">
                <option value="">全部</option>
                <?php foreach (array_keys($summary['products']) as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
            </select>
            <label>状态:</label>
            <select id="filterStatus" onchange="applyFilters()">
                <option value="">全部</option>
                <?php foreach (array_keys($summary['conversion_statuses']) as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="applyFilters()">筛选</button>
            <button class="secondary" onclick="resetFilters()">重置</button>
        </div>

        <div class="export-bar">
            <button onclick="exportTableCSV()">📊 导出当前数据 CSV</button>
            <button onclick="window.print()">🖨️ 打印报告</button>
        </div>

        <!-- KPI 卡片 -->
        <div class="kpi-grid">
            <div class="kpi-card green">
                <div class="label">总询盘数</div>
                <div class="value"><?= InquiryReportGenerator::formatNumber($summary['total_inquiries']) ?></div>
                <div class="sub">条</div>
            </div>
            <div class="kpi-card blue">
                <div class="label">独立公司</div>
                <div class="value"><?= InquiryReportGenerator::formatNumber($summary['unique_companies']) ?></div>
                <div class="sub">家</div>
            </div>
            <div class="kpi-card orange">
                <div class="label">总预估金额</div>
                <div class="value"><?= InquiryReportGenerator::formatMoney($summary['total_estimated_value']) ?></div>
                <div class="sub">USD</div>
            </div>
            <div class="kpi-card purple">
                <div class="label">平均询盘价值</div>
                <div class="value"><?= InquiryReportGenerator::formatMoney($summary['avg_estimated_value']) ?></div>
                <div class="sub">USD / 条</div>
            </div>
            <div class="kpi-card teal">
                <div class="label">平均响应时间</div>
                <div class="value"><?= $summary['response_time_stats']['avg'] ?></div>
                <div class="sub">小时（中位数: <?= $summary['response_time_stats']['median'] ?>h）</div>
            </div>
            <div class="kpi-card red">
                <div class="label">成交转化率</div>
                <div class="value"><?php
                    $closed = ($summary['conversion_statuses']['已成交'] ?? 0) + ($summary['conversion_statuses']['已下单'] ?? 0);
                    echo $summary['total_inquiries'] > 0 ? round($closed / $summary['total_inquiries'] * 100, 1) . '%' : '0%';
                ?></div>
                <div class="sub">已下单 + 已成交</div>
            </div>
        </div>

        <!-- 图表区域 -->
        <div class="charts-grid">
            <!-- 转化漏斗 -->
            <div class="chart-card">
                <h3>🔻 转化漏斗</h3>
                <div class="funnel" id="funnelChart">
                    <?php
                    $funnelColors = ['#1a73e8', '#0d9488', '#34a853', '#fbbc04', '#ea580c', '#dc2626', '#7c3aed', '#1b5e20', '#757575'];
                    $maxFunnelCount = max(array_column($summary['conversion_funnel'], 'count')) ?: 1;
                    $i = 0;
                    foreach ($summary['conversion_funnel'] as $stage => $data):
                        if ($data['count'] === 0 && $stage === '已关闭') continue;
                        $width = max(60, ($data['count'] / $maxFunnelCount) * 100);
                        $color = $funnelColors[$i % count($funnelColors)];
                    ?>
                    <div class="funnel-stage">
                        <div class="funnel-label"><?= htmlspecialchars($stage) ?></div>
                        <div class="funnel-bar" style="width:<?= $width ?>%;background:<?= $color ?>;"><?= $data['count'] ?></div>
                        <div class="funnel-pct"><?= $data['percentage'] ?>%</div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>

            <!-- 来源渠道分布 -->
            <div class="chart-card">
                <h3>📡 询盘来源渠道</h3>
                <div id="chartSource" style="height:300px;"></div>
            </div>

            <!-- 月度趋势 -->
            <div class="chart-card full-width">
                <h3>📈 月度询盘趋势</h3>
                <div id="chartMonthly" style="height:300px;"></div>
            </div>

            <!-- 产品分布 -->
            <div class="chart-card">
                <h3>📦 产品兴趣分布</h3>
                <div id="chartProduct" style="height:300px;"></div>
            </div>

            <!-- 国家分布 -->
            <div class="chart-card">
                <h3>🌍 国家分布 Top 10</h3>
                <div id="chartCountry" style="height:300px;"></div>
            </div>

            <!-- 响应时间分布 -->
            <div class="chart-card">
                <h3>⏱️ 响应时间分布</h3>
                <div id="chartResponse" style="height:300px;"></div>
            </div>

            <!-- 询盘类型 -->
            <div class="chart-card">
                <h3>📝 询盘类型分布</h3>
                <div id="chartType" style="height:300px;"></div>
            </div>
        </div>

        <!-- 来源渠道 ROI 分析 -->
        <div class="table-card">
            <h3>💰 来源渠道 ROI 分析</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>排名</th>
                            <th>渠道</th>
                            <th>来源类型</th>
                            <th>询盘数</th>
                            <th>总预估金额</th>
                            <th>平均金额</th>
                            <th>成交数</th>
                            <th>转化率</th>
                            <th style="width:150px;">金额占比</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $maxValue = max(array_column($summary['source_roi'], 'total_value')) ?: 1;
                        $i = 0;
                        foreach (array_slice($summary['source_roi'], 0, 15) as $roi):
                            $pct = $summary['total_estimated_value'] > 0 ? round($roi['total_value'] / $summary['total_estimated_value'] * 100, 1) : 0;
                            $barW = round($roi['total_value'] / $maxValue * 100);
                        ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><strong><?= htmlspecialchars($roi['channel']) ?></strong></td>
                            <td><?= htmlspecialchars($roi['type']) ?></td>
                            <td><?= number_format($roi['inquiries']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($roi['total_value']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($roi['avg_value']) ?></td>
                            <td><?= $roi['converted'] ?></td>
                            <td><strong><?= $roi['conversion_rate'] ?>%</strong></td>
                            <td>
                                <div class="rank-bar">
                                    <div class="bar"><div class="bar-fill" style="width:<?= $barW ?>%;background:#0d9488;"></div></div>
                                    <span class="count"><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 产品价值排名 -->
        <div class="table-card">
            <h3>📦 产品价值排名</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>排名</th>
                            <th>产品</th>
                            <th>询盘数</th>
                            <th>总预估金额</th>
                            <th>平均金额</th>
                            <th>成交数</th>
                            <th>转化率</th>
                            <th style="width:150px;">金额占比</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $maxProdValue = max(array_column($summary['product_value'], 'total_value')) ?: 1;
                        $i = 0;
                        foreach ($summary['product_value'] as $product => $data):
                            $pct = $summary['total_estimated_value'] > 0 ? round($data['total_value'] / $summary['total_estimated_value'] * 100, 1) : 0;
                            $barW = round($data['total_value'] / $maxProdValue * 100);
                        ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><strong><?= htmlspecialchars($product) ?></strong></td>
                            <td><?= number_format($data['count']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($data['total_value']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($data['avg_value']) ?></td>
                            <td><?= $data['converted'] ?></td>
                            <td><strong><?= $data['conversion_rate'] ?>%</strong></td>
                            <td>
                                <div class="rank-bar">
                                    <div class="bar"><div class="bar-fill" style="width:<?= $barW ?>%;background:#7c3aed;"></div></div>
                                    <span class="count"><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 国家价值排名 -->
        <div class="table-card">
            <h3>🌍 国家价值排名</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>排名</th>
                            <th>国家</th>
                            <th>询盘数</th>
                            <th>总预估金额</th>
                            <th>平均金额</th>
                            <th>成交数</th>
                            <th>转化率</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach ($summary['country_value'] as $country => $data): ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><strong><?= htmlspecialchars($country) ?></strong></td>
                            <td><?= number_format($data['count']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($data['total_value']) ?></td>
                            <td><?= InquiryReportGenerator::formatMoney($data['avg_value']) ?></td>
                            <td><?= $data['converted'] ?></td>
                            <td><strong><?= $data['conversion_rate'] ?>%</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 详细数据表格 -->
        <div class="table-card">
            <h3>📋 详细询盘数据 (<span id="recordCount"><?= count($records) ?></span> 条)</h3>
            <div class="table-wrap">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">时间</th>
                            <th onclick="sortTable(1)">公司</th>
                            <th onclick="sortTable(2)">联系人</th>
                            <th onclick="sortTable(3)">国家</th>
                            <th onclick="sortTable(4)">产品</th>
                            <th onclick="sortTable(5)">来源</th>
                            <th onclick="sortTable(6)">类型</th>
                            <th onclick="sortTable(7)">金额</th>
                            <th onclick="sortTable(8)">状态</th>
                        </tr>
                    </thead>
                    <tbody id="dataBody"></tbody>
                </table>
            </div>
            <div class="pagination">
                <div class="info">显示 <span id="pagInfo">1-20 / 0</span></div>
                <div class="pages" id="pagButtons"></div>
            </div>
        </div>
    </div>

    <script>
        const allRecords = <?= json_encode($records, JSON_UNESCAPED_UNICODE) ?>;
        let filteredRecords = [...allRecords];
        let currentPage = 1;
        const perPage = 20;
        let sortCol = -1;
        let sortAsc = true;

        const statusBadgeMap = {
            '新询盘': 'new', '已回复': 'replied', '报价中': 'quoting', '已报价': 'quoted',
            '样品阶段': 'sample', '谈判中': 'negotiating', '已下单': 'ordered', '已成交': 'closed', '已关闭': 'lost'
        };

        google.charts.load('current', { packages: ['corechart', 'bar'] });
        google.charts.setOnLoadCallback(drawAllCharts);

        function drawAllCharts() {
            drawSourceChart();
            drawMonthlyChart();
            drawProductChart();
            drawCountryChart();
            drawResponseChart();
            drawTypeChart();
            renderTable();
        }

        function drawSourceChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '来源类型');
            data.addColumn('number', '询盘数');
            <?php foreach ($summary['source_types'] as $type => $count): ?>
            data.addRow(['<?= addslashes($type) ?>', <?= $count ?>]);
            <?php endforeach; ?>

            new google.visualization.PieChart(document.getElementById('chartSource')).draw(data, {
                pieHole: 0.4,
                colors: ['#1a73e8', '#34a853', '#ea4335', '#fbbc04', '#9334e6', '#ff6d01', '#46bdc6', '#e91e63'],
                legend: { position: 'right', textStyle: { fontSize: 12 } },
                chartArea: { left: 20, top: 10, width: '80%', height: '85%' }
            });
        }

        function drawMonthlyChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '月份');
            data.addColumn('number', '询盘数');
            data.addColumn('number', '金额(USD)');
            <?php foreach ($summary['monthly_trend'] as $month => $stat): ?>
            data.addRow(['<?= $month ?>', <?= $stat['count'] ?>, <?= round($stat['value']) ?>]);
            <?php endforeach; ?>

            new google.visualization.ComboChart(document.getElementById('chartMonthly')).draw(data, {
                colors: ['#1a73e8', '#0d9488'],
                seriesType: 'bars',
                series: { 1: { type: 'line', targetAxisIndex: 1 } },
                vAxes: { 0: { title: '询盘数', textStyle: { fontSize: 11 } }, 1: { title: '金额(USD)', textStyle: { fontSize: 11 } } },
                legend: { position: 'top', textStyle: { fontSize: 12 } },
                chartArea: { left: 60, top: 40, width: '80%', height: '70%' },
                hAxis: { textStyle: { fontSize: 10 } }
            });
        }

        function drawProductChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '产品');
            data.addColumn('number', '询盘数');
            <?php
            $topProducts = InquiryReportGenerator::topN($summary['products'], 8);
            foreach ($topProducts as $product => $count):
            ?>
            data.addRow(['<?= addslashes($product) ?>', <?= $count ?>]);
            <?php endforeach; ?>

            new google.visualization.BarChart(document.getElementById('chartProduct')).draw(data, {
                colors: ['#7c3aed'],
                legend: 'none',
                chartArea: { left: 120, top: 10, width: '75%', height: '80%' }
            });
        }

        function drawCountryChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '国家');
            data.addColumn('number', '询盘数');
            <?php
            $topCountries = InquiryReportGenerator::topN($summary['countries'], 10);
            foreach ($topCountries as $country => $count):
            ?>
            data.addRow(['<?= addslashes($country) ?>', <?= $count ?>]);
            <?php endforeach; ?>

            new google.visualization.BarChart(document.getElementById('chartCountry')).draw(data, {
                colors: ['#0d9488'],
                legend: 'none',
                chartArea: { left: 80, top: 10, width: '75%', height: '80%' }
            });
        }

        function drawResponseChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '时间段');
            data.addColumn('number', '数量');
            <?php foreach ($summary['response_time_stats']['distribution'] as $range => $count): ?>
            data.addRow(['<?= addslashes($range) ?>', <?= $count ?>]);
            <?php endforeach; ?>

            new google.visualization.ColumnChart(document.getElementById('chartResponse')).draw(data, {
                colors: ['#0d9488'],
                legend: 'none',
                chartArea: { left: 50, top: 10, width: '85%', height: '80%' },
                hAxis: { textStyle: { fontSize: 10 } },
                vAxis: { textStyle: { fontSize: 11 } }
            });
        }

        function drawTypeChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', '类型');
            data.addColumn('number', '数量');
            <?php foreach ($summary['inquiry_types'] as $type => $count): ?>
            data.addRow(['<?= addslashes($type) ?>', <?= $count ?>]);
            <?php endforeach; ?>

            new google.visualization.PieChart(document.getElementById('chartType')).draw(data, {
                colors: ['#1a73e8', '#34a853', '#ea4335', '#fbbc04', '#9334e6', '#ff6d01', '#46bdc6'],
                legend: { position: 'right', textStyle: { fontSize: 12 } },
                chartArea: { left: 20, top: 10, width: '80%', height: '85%' }
            });
        }

        function applyFilters() {
            const country = document.getElementById('filterCountry').value;
            const source = document.getElementById('filterSource').value;
            const product = document.getElementById('filterProduct').value;
            const status = document.getElementById('filterStatus').value;

            filteredRecords = allRecords.filter(r => {
                if (country && r.country !== country) return false;
                if (source && r.source_type !== source) return false;
                if (product && r.product_interest !== product) return false;
                if (status && r.conversion_status !== status) return false;
                return true;
            });
            currentPage = 1;
            document.getElementById('recordCount').textContent = filteredRecords.length;
            renderTable();
        }

        function resetFilters() {
            document.getElementById('filterCountry').value = '';
            document.getElementById('filterSource').value = '';
            document.getElementById('filterProduct').value = '';
            document.getElementById('filterStatus').value = '';
            filteredRecords = [...allRecords];
            currentPage = 1;
            document.getElementById('recordCount').textContent = filteredRecords.length;
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('dataBody');
            const start = (currentPage - 1) * perPage;
            const end = Math.min(start + perPage, filteredRecords.length);
            const pageRecords = filteredRecords.slice(start, end);

            tbody.innerHTML = pageRecords.map(r => {
                const badge = statusBadgeMap[r.conversion_status] || 'new';
                const val = r.estimated_value ? '$' + Number(r.estimated_value).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '-';
                return `<tr>
                    <td>${esc(r.timestamp)}</td>
                    <td>${esc(r.company)}</td>
                    <td>${esc(r.contact_name)}</td>
                    <td>${esc(r.country)}</td>
                    <td>${esc(r.product_interest)}</td>
                    <td>${esc(r.source_channel)}</td>
                    <td>${esc(r.inquiry_type)}</td>
                    <td>${val}</td>
                    <td><span class="badge badge-${badge}">${esc(r.conversion_status)}</span></td>
                </tr>`;
            }).join('');

            document.getElementById('pagInfo').textContent = `${filteredRecords.length > 0 ? start + 1 : 0}-${end} / ${filteredRecords.length}`;
            const totalPages = Math.max(1, Math.ceil(filteredRecords.length / perPage));
            const pagDiv = document.getElementById('pagButtons');
            let btns = `<button onclick="goPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>‹</button>`;
            let sp = Math.max(1, currentPage - 3), ep = Math.min(totalPages, sp + 6);
            if (ep - sp < 6) sp = Math.max(1, ep - 6);
            for (let i = sp; i <= ep; i++) btns += `<button onclick="goPage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
            btns += `<button onclick="goPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>›</button>`;
            pagDiv.innerHTML = btns;
        }

        function goPage(p) { if (p >= 1 && p <= Math.ceil(filteredRecords.length / perPage)) { currentPage = p; renderTable(); } }

        function sortTable(ci) {
            const fields = ['timestamp', 'company', 'contact_name', 'country', 'product_interest', 'source_channel', 'inquiry_type', 'estimated_value', 'conversion_status'];
            const f = fields[ci];
            if (sortCol === ci) sortAsc = !sortAsc; else { sortCol = ci; sortAsc = true; }
            filteredRecords.sort((a, b) => {
                let va = a[f] || '', vb = b[f] || '';
                if (f === 'estimated_value') return sortAsc ? va - vb : vb - va;
                return sortAsc ? String(va).localeCompare(String(vb)) : String(vb).localeCompare(String(va));
            });
            currentPage = 1;
            renderTable();
        }

        function exportTableCSV() {
            let csv = '﻿询盘时间,公司名称,联系人,国家,产品,来源渠道,询盘类型,预估金额(USD),转化状态\n';
            filteredRecords.forEach(r => {
                csv += [r.timestamp, r.company, r.contact_name, r.country, r.product_interest, r.source_channel, r.inquiry_type, r.estimated_value, r.conversion_status]
                    .map(v => `"${(v || '').toString().replace(/"/g, '""')}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'inquiry_report_' + new Date().toISOString().slice(0, 10) + '.csv';
            link.click();
        }

        function esc(s) { return s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''; }
    </script>
</body>
</html>
