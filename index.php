<?php
/**
 * 询盘数据分析 - 主入口
 * 支持 CSV 上传、数据分析、报告生成
 */

require_once __DIR__ . '/InquiryAnalyzer.php';
require_once __DIR__ . '/InquiryReportGenerator.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);

$analyzer = new InquiryAnalyzer();
$message = '';
$messageType = '';
$step = 'upload';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_csv') {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $message = '请上传有效的 CSV 文件。';
            $messageType = 'error';
        } else {
            $file = $_FILES['csv_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'csv') {
                $message = '仅支持 CSV 格式文件。';
                $messageType = 'error';
            } elseif ($file['size'] > 50 * 1024 * 1024) {
                $message = '文件大小不能超过 50MB。';
                $messageType = 'error';
            } else {
                $uploadDir = __DIR__ . '/data';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $savePath = $uploadDir . '/uploaded_' . time() . '.csv';
                move_uploaded_file($file['tmp_name'], $savePath);

                try {
                    $count = $analyzer->importCSV($savePath);
                    $summary = $analyzer->getSummary();
                    $records = $analyzer->getRecords();

                    $reportGen = new InquiryReportGenerator($records, $summary);
                    $reportFile = __DIR__ . '/reports/inquiry_report_' . date('Ymd_His') . '.html';
                    $reportGen->saveToFile($reportFile);

                    $message = "分析完成！已处理 {$count} 条询盘，总预估金额 $" . number_format($summary['total_estimated_value'], 2) . " USD。";
                    $messageType = 'success';
                    $step = 'done';
                    $reportPath = basename($reportFile);

                } catch (Exception $e) {
                    $message = '数据处理出错: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }

    if ($action === 'use_sample') {
        $count = max(50, min(3000, (int)($_POST['sample_count'] ?? 300)));
        $sampleFile = __DIR__ . '/data/sample_inquiries.csv';

        // 重新生成示例数据
        ob_start();
        $argc = 4;
        $argv = ['', (string)$count, date('Y-m-d', strtotime('-90 days')), date('Y-m-d')];
        include __DIR__ . '/generate-sample.php';
        ob_end_clean();

        if (file_exists($sampleFile)) {
            try {
                $count = $analyzer->importCSV($sampleFile);
                $summary = $analyzer->getSummary();
                $records = $analyzer->getRecords();

                $reportGen = new InquiryReportGenerator($records, $summary);
                $reportFile = __DIR__ . '/reports/inquiry_report_' . date('Ymd_His') . '.html';
                $reportGen->saveToFile($reportFile);

                $message = "示例数据分析完成！{$count} 条询盘，总预估金额 $" . number_format($summary['total_estimated_value'], 2) . " USD。";
                $messageType = 'success';
                $step = 'done';
                $reportPath = basename($reportFile);

            } catch (Exception $e) {
                $message = '数据处理出错: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = '示例数据生成失败。';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>询盘数据分析系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f0f2f5; color: #333; min-height: 100vh; display: flex; flex-direction: column; }

        .app-header { background: linear-gradient(135deg, #0d9488 0%, #115e59 100%); color: white; padding: 40px 20px; text-align: center; }
        .app-header h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .app-header p { font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto; }
        .app-header .features { display: flex; gap: 24px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
        .app-header .feature { display: flex; align-items: center; gap: 6px; font-size: 14px; opacity: 0.85; }

        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; flex: 1; }

        .card { background: white; border-radius: 12px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .card h2 { font-size: 20px; margin-bottom: 20px; color: #0d9488; }

        .upload-zone { border: 2px dashed #d0d5dd; border-radius: 8px; padding: 48px 24px; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; }
        .upload-zone:hover { border-color: #0d9488; background: #f0fdfa; }
        .upload-zone.dragover { border-color: #0d9488; background: #ccfbf1; }
        .upload-zone .icon { font-size: 48px; margin-bottom: 16px; }
        .upload-zone .text { font-size: 16px; color: #555; margin-bottom: 8px; }
        .upload-zone .hint { font-size: 13px; color: #999; }
        .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .selected-file { margin-top: 16px; padding: 12px 16px; background: #f0fdfa; border-radius: 6px; display: none; font-size: 14px; color: #0d9488; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 1px solid #d0d5dd; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,0.1); }

        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 24px; border: none; border-radius: 8px; font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #0d9488; color: white; }
        .btn-primary:hover { background: #0f766e; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13,148,136,0.3); }
        .btn-sample { background: #7c3aed; color: white; }
        .btn-sample:hover { background: #6d28d9; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(124,58,237,0.3); }

        .btn-group { display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap; }

        .message { padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; }
        .message.success { background: #f0fdfa; color: #115e59; border-left: 4px solid #0d9488; }
        .message.error { background: #fef2f2; color: #dc2626; border-left: 4px solid #ef4444; }

        .divider { display: flex; align-items: center; gap: 16px; margin: 32px 0; color: #999; font-size: 14px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e0e0e0; }

        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 24px; }
        .feature-card { background: #f8f9fa; border-radius: 8px; padding: 20px; text-align: center; }
        .feature-card .icon { font-size: 32px; margin-bottom: 8px; }
        .feature-card h4 { font-size: 15px; margin-bottom: 4px; color: #333; }
        .feature-card p { font-size: 13px; color: #666; }

        .format-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 13px; }
        .format-table th, .format-table td { padding: 8px 12px; border: 1px solid #e0e0e0; text-align: left; }
        .format-table th { background: #f8f9fa; font-weight: 600; }
        .format-table code { background: #f0f0f0; padding: 1px 4px; border-radius: 3px; font-size: 12px; }

        .app-footer { text-align: center; padding: 20px; font-size: 13px; color: #999; }
    </style>
</head>
<body>
    <div class="app-header">
        <h1>📊 询盘数据分析系统</h1>
        <p>多维度分析 B2B 询盘数据，追踪转化漏斗，优化营销投入</p>
        <div class="features">
            <div class="feature">✓ 来源渠道分析</div>
            <div class="feature">✓ 转化漏斗追踪</div>
            <div class="feature">✓ 产品需求洞察</div>
            <div class="feature">✓ 响应效率监控</div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
            <?php if ($step === 'done' && isset($reportPath)): ?>
            <br><a href="reports/<?= htmlspecialchars($reportPath) ?>" target="_blank" style="color:inherit;font-weight:600;">📊 点击查看完整报告 →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($step === 'upload'): ?>
        <div class="card">
            <h2>📁 上传询盘数据</h2>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="action" value="upload_csv">

                <div class="upload-zone" id="uploadZone">
                    <div class="icon">📂</div>
                    <div class="text">点击或拖拽上传 CSV 文件</div>
                    <div class="hint">支持 UTF-8 编码，最大 50MB</div>
                    <input type="file" name="csv_file" accept=".csv" id="fileInput">
                    <div class="selected-file" id="selectedFile"></div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">🚀 开始分析</button>
                </div>
            </form>
        </div>

        <div class="divider">或者使用示例数据</div>

        <div class="card">
            <h2>🧪 使用示例数据</h2>
            <p style="font-size:14px;color:#666;margin-bottom:20px;">
                生成模拟 B2B 询盘数据来体验系统功能，包含多种来源渠道、产品类别和转化状态。
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="use_sample">
                <div class="form-group">
                    <label>生成记录数</label>
                    <select name="sample_count">
                        <option value="100">100 条</option>
                        <option value="300" selected>300 条（推荐）</option>
                        <option value="500">500 条</option>
                        <option value="1000">1,000 条</option>
                        <option value="2000">2,000 条</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-sample">📊 生成并分析</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>📋 CSV 格式说明</h2>
            <p style="font-size:14px;color:#666;margin-bottom:16px;">
                系统支持自动识别以下列名（不区分大小写，支持中英文表头）：
            </p>
            <table class="format-table">
                <thead>
                    <tr><th>列名</th><th>别名</th><th>说明</th><th>必需</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>询盘时间</code></td><td>timestamp, date, time</td><td>询盘提交时间</td><td>✅ 是</td></tr>
                    <tr><td><code>公司名称</code></td><td>company, company_name</td><td>询盘公司名称</td><td>✅ 是</td></tr>
                    <tr><td><code>联系人</code></td><td>contact_name, name</td><td>联系人姓名</td><td>✅ 是</td></tr>
                    <tr><td><code>邮箱</code></td><td>email, e-mail</td><td>联系邮箱</td><td>建议</td></tr>
                    <tr><td><code>国家</code></td><td>country, country_name</td><td>客户所在国家</td><td>✅ 是</td></tr>
                    <tr><td><code>感兴趣产品</code></td><td>product_interest, product</td><td>客户感兴趣的产品</td><td>✅ 是</td></tr>
                    <tr><td><code>询盘类型</code></td><td>inquiry_type, type</td><td>询价/咨询/样品/合作等</td><td>建议</td></tr>
                    <tr><td><code>来源渠道</code></td><td>source_channel, channel</td><td>Google Ads/Alibaba/展会等</td><td>✅ 是</td></tr>
                    <tr><td><code>来源类型</code></td><td>source_type</td><td>付费广告/B2B平台/自然搜索等</td><td>建议</td></tr>
                    <tr><td><code>预估金额</code></td><td>estimated_value_usd, value</td><td>预估成交金额 (USD)</td><td>建议</td></tr>
                    <tr><td><code>转化状态</code></td><td>conversion_status, status</td><td>新询盘/已回复/报价中/已成交等</td><td>✅ 是</td></tr>
                    <tr><td><code>回复时间</code></td><td>response_time_hours</td><td>首次回复耗时（小时）</td><td>可选</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>✨ 分析功能</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="icon">🔻</div>
                    <h4>转化漏斗</h4>
                    <p>追踪从新询盘到成交的完整转化路径</p>
                </div>
                <div class="feature-card">
                    <div class="icon">💰</div>
                    <h4>渠道 ROI</h4>
                    <p>按来源渠道分析询盘价值和转化率</p>
                </div>
                <div class="feature-card">
                    <div class="icon">📦</div>
                    <h4>产品洞察</h4>
                    <p>分析产品需求热度和价值分布</p>
                </div>
                <div class="feature-card">
                    <div class="icon">⏱️</div>
                    <h4>响应效率</h4>
                    <p>监控询盘响应时间分布和趋势</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="app-footer">
        询盘数据分析系统 v1.0 | 支持 PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadZone = document.getElementById('uploadZone');
        const selectedFile = document.getElementById('selectedFile');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files[0]) {
                    selectedFile.textContent = '✓ 已选择: ' + this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(1) + ' KB)';
                    selectedFile.style.display = 'block';
                }
            });

            uploadZone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
            uploadZone.addEventListener('dragleave', function() { this.classList.remove('dragover'); });
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                if (e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        }
    </script>
</body>
</html>
