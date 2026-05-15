<?php
/**
 * 询盘数据分析 - 示例数据生成器
 * 生成模拟 B2B 询盘/询价数据
 */

// 产品类别
$products = [
    ['name' => 'LED照明产品', 'weight' => 20, 'price_range' => [5, 150]],
    ['name' => '工业自动化设备', 'weight' => 12, 'price_range' => [500, 50000]],
    ['name' => '电子元器件', 'weight' => 15, 'price_range' => [0.5, 50]],
    ['name' => '机械设备', 'weight' => 10, 'price_range' => [1000, 100000]],
    ['name' => '汽车配件', 'weight' => 8, 'price_range' => [10, 500]],
    ['name' => '医疗设备', 'weight' => 6, 'price_range' => [200, 80000]],
    ['name' => '安防监控设备', 'weight' => 7, 'price_range' => [50, 5000]],
    ['name' => '新能源产品', 'weight' => 8, 'price_range' => [100, 30000]],
    ['name' => '包装材料', 'weight' => 5, 'price_range' => [1, 20]],
    ['name' => '建筑材料', 'weight' => 4, 'price_range' => [5, 200]],
    ['name' => '纺织品', 'weight' => 3, 'price_range' => [2, 50]],
    ['name' => '化工原料', 'weight' => 2, 'price_range' => [10, 1000]],
];

// 来源渠道
$sources = [
    ['name' => 'Google Ads', 'type' => '付费广告', 'weight' => 15],
    ['name' => '百度推广', 'type' => '付费广告', 'weight' => 10],
    ['name' => 'Google 自然搜索', 'type' => '自然搜索', 'weight' => 12],
    ['name' => '百度自然搜索', 'type' => '自然搜索', 'weight' => 5],
    ['name' => 'Alibaba.com', 'type' => 'B2B平台', 'weight' => 15],
    ['name' => 'Made-in-China', 'type' => 'B2B平台', 'weight' => 8],
    ['name' => 'GlobalSources', 'type' => 'B2B平台', 'weight' => 5],
    ['name' => 'LinkedIn', 'type' => '社交媒体', 'weight' => 6],
    ['name' => 'Facebook', 'type' => '社交媒体', 'weight' => 4],
    ['name' => 'EDM邮件营销', 'type' => '邮件营销', 'weight' => 5],
    ['name' => '展会', 'type' => '线下渠道', 'weight' => 8],
    ['name' => '老客户推荐', 'type' => '推荐', 'weight' => 5],
    ['name' => '直接访问官网', 'type' => '直接访问', 'weight' => 2],
];

// 国家分布
$countries = [
    ['country' => '美国', 'code' => 'US', 'weight' => 15],
    ['country' => '英国', 'code' => 'GB', 'weight' => 8],
    ['country' => '德国', 'code' => 'DE', 'weight' => 7],
    ['country' => '印度', 'code' => 'IN', 'weight' => 12],
    ['country' => '巴西', 'code' => 'BR', 'weight' => 6],
    ['country' => '日本', 'code' => 'JP', 'weight' => 6],
    ['country' => '韩国', 'code' => 'KR', 'weight' => 5],
    ['country' => '澳大利亚', 'code' => 'AU', 'weight' => 5],
    ['country' => '加拿大', 'code' => 'CA', 'weight' => 4],
    ['country' => '法国', 'code' => 'FR', 'weight' => 4],
    ['country' => '俄罗斯', 'code' => 'RU', 'weight' => 5],
    ['country' => '阿联酋', 'code' => 'AE', 'weight' => 4],
    ['country' => '沙特阿拉伯', 'code' => 'SA', 'weight' => 3],
    ['country' => '墨西哥', 'code' => 'MX', 'weight' => 3],
    ['country' => '尼日利亚', 'code' => 'NG', 'weight' => 3],
    ['country' => '土耳其', 'code' => 'TR', 'weight' => 3],
    ['country' => '越南', 'code' => 'VN', 'weight' => 3],
    ['country' => '泰国', 'code' => 'TH', 'weight' => 2],
    ['country' => '印度尼西亚', 'code' => 'ID', 'weight' => 2],
    ['country' => '南非', 'code' => 'ZA', 'weight' => 2],
    ['country' => '波兰', 'code' => 'PL', 'weight' => 2],
];

// 询盘类型
$inquiryTypes = [
    ['type' => '询价报价', 'weight' => 35],
    ['type' => '产品咨询', 'weight' => 25],
    ['type' => '样品申请', 'weight' => 15],
    ['type' => '技术咨询', 'weight' => 10],
    ['type' => '合作洽谈', 'weight' => 8],
    ['type' => '售后服务', 'weight' => 5],
    ['type' => '定制需求', 'weight' => 2],
];

// 转化阶段
$statuses = [
    ['status' => '新询盘', 'weight' => 25],
    ['status' => '已回复', 'weight' => 20],
    ['status' => '报价中', 'weight' => 15],
    ['status' => '已报价', 'weight' => 12],
    ['status' => '样品阶段', 'weight' => 8],
    ['status' => '谈判中', 'weight' => 7],
    ['status' => '已下单', 'weight' => 6],
    ['status' => '已成交', 'weight' => 4],
    ['status' => '已关闭', 'weight' => 3],
];

// 公司名称模板
$companyPrefixes = ['Global', 'Prime', 'Star', 'Elite', 'Alpha', 'Beta', 'Delta', 'Sigma', 'Omega', 'Max', 'Pro', 'Tech', 'Smart', 'Neo', 'Meta'];
$companySuffixes = ['Trading', 'Industries', 'Corp', 'Enterprises', 'Solutions', 'Tech', 'Group', 'International', 'Co Ltd', 'Import', 'Supply', 'Manufacturing', 'Systems', 'Electronics', 'Engineering'];

// 姓名模板（按国家）
$namesByCountry = [
    'US' => [['John', 'Smith'], ['Michael', 'Johnson'], ['David', 'Williams'], ['Robert', 'Brown'], ['James', 'Davis'], ['Sarah', 'Miller'], ['Emily', 'Wilson']],
    'GB' => [['James', 'Taylor'], ['Oliver', 'Thomas'], ['George', 'Wilson'], ['Emily', 'Jones'], ['Charlotte', 'Brown']],
    'DE' => [['Hans', 'Mueller'], ['Friedrich', 'Schmidt'], ['Klaus', 'Weber'], ['Anna', 'Fischer'], ['Maria', 'Wagner']],
    'IN' => [['Raj', 'Patel'], ['Amit', 'Kumar'], ['Priya', 'Sharma'], ['Rahul', 'Gupta'], ['Neha', 'Singh']],
    'JP' => [['Takeshi', 'Yamamoto'], ['Hiroshi', 'Tanaka'], ['Yuki', 'Sato'], ['Kenji', 'Suzuki']],
    'KR' => [['Min-Jun', 'Kim'], ['Ji-Hoon', 'Lee'], ['Seo-Yeon', 'Park'], ['Dong-Hyun', 'Choi']],
    'BR' => [['Carlos', 'Silva'], ['Pedro', 'Santos'], ['Ana', 'Oliveira'], ['Maria', 'Souza']],
    'default' => [['Alex', 'Johnson'], ['Sam', 'Wilson'], ['Chris', 'Taylor'], ['Pat', 'Anderson']],
];

// 权重随机选择
function weightedRandom(array $items): array {
    $totalWeight = array_sum(array_column($items, 'weight'));
    $random = mt_rand(1, $totalWeight);
    $cumulative = 0;
    foreach ($items as $item) {
        $cumulative += $item['weight'];
        if ($random <= $cumulative) {
            return $item;
        }
    }
    return end($items);
}

// 生成询盘数据
function generateInquiries(int $count, string $startDate, string $endDate): array {
    global $products, $sources, $countries, $inquiryTypes, $statuses, $companyPrefixes, $companySuffixes, $namesByCountry;

    $start = strtotime($startDate);
    $end = strtotime($endDate);
    $dateRange = $end - $start;

    $records = [];
    for ($i = 0; $i < $count; $i++) {
        $product = weightedRandom($products);
        $source = weightedRandom($sources);
        $country = weightedRandom($countries);
        $inquiryType = weightedRandom($inquiryTypes);
        $status = weightedRandom($statuses);

        // 生成时间戳（工作日/工作时间权重更高）
        $timestamp = $start + mt_rand(0, $dateRange);
        $dow = (int)date('N', $timestamp);
        // 周末降低概率
        if ($dow >= 6 && mt_rand(1, 100) <= 60) {
            $timestamp = $start + mt_rand(0, $dateRange);
        }

        // 生成公司名称
        $company = $companyPrefixes[array_rand($companyPrefixes)] . ' ' . $companySuffixes[array_rand($companySuffixes)];

        // 生成联系人姓名
        $code = $country['code'];
        $namePool = $namesByCountry[$code] ?? $namesByCountry['default'];
        $name = $namePool[array_rand($namePool)];

        // 生成邮箱
        $emailDomain = strtolower(str_replace(' ', '', $company)) . '.com';
        $email = strtolower($name[0]) . '.' . strtolower($name[1]) . '@' . $emailDomain;

        // 生成数量和金额
        $qtyMin = mt_rand(1, 100);
        $qtyUnit = ['件', '套', '台', '吨', '箱', '米'][array_rand(['件', '套', '台', '吨', '箱', '米'])];
        $unitPrice = $product['price_range'][0] + mt_rand(0, (int)(($product['price_range'][1] - $product['price_range'][0]) * 100)) / 100;
        $totalValue = round($qtyMin * $unitPrice, 2);

        // 回复时间（小时，已回复的才有值）
        $responseHours = null;
        if (!in_array($status['status'], ['新询盘'])) {
            $responseHours = mt_rand(1, 72);
        }

        $records[] = [
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'company' => $company,
            'contact_name' => $name[0] . ' ' . $name[1],
            'email' => $email,
            'country' => $country['country'],
            'country_code' => $code,
            'product_interest' => $product['name'],
            'inquiry_type' => $inquiryType['type'],
            'source_channel' => $source['name'],
            'source_type' => $source['type'],
            'quantity' => $qtyMin,
            'unit' => $qtyUnit,
            'estimated_value_usd' => $totalValue,
            'conversion_status' => $status['status'],
            'response_time_hours' => $responseHours,
            'notes' => '',
        ];
    }

    // 按时间排序
    usort($records, fn($a, $b) => strcmp($a['timestamp'], $b['timestamp']));
    return $records;
}

// 主执行逻辑
$count = isset($argv[1]) ? (int)$argv[1] : 300;
$startDate = $argv[2] ?? date('Y-m-d', strtotime('-90 days'));
$endDate = $argv[3] ?? date('Y-m-d');

echo "正在生成 {$count} 条示例询盘数据...\n";
echo "时间范围: {$startDate} ~ {$endDate}\n";

$records = generateInquiries($count, $startDate, $endDate);

$outputDir = __DIR__ . '/data';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$filename = $outputDir . '/sample_inquiries.csv';
$fp = fopen($filename, 'w');
fwrite($fp, "\xEF\xBB\xBF");

$headers = ['询盘时间', '公司名称', '联系人', '邮箱', '国家', '国家代码', '感兴趣产品', '询盘类型', '来源渠道', '来源类型', '数量', '单位', '预估金额(USD)', '转化状态', '回复时间(小时)', '备注'];
fputcsv($fp, $headers);

foreach ($records as $record) {
    fputcsv($fp, array_values($record));
}

fclose($fp);

echo "✓ 已生成: {$filename}\n";
echo "  记录数: " . count($records) . "\n";
echo "  文件大小: " . round(filesize($filename) / 1024, 1) . " KB\n";

// 统计摘要
$totalValue = array_sum(array_column($records, 'estimated_value_usd'));
$countries = array_count_values(array_column($records, 'country'));
arsort($countries);
$statuses = array_count_values(array_column($records, 'conversion_status'));

echo "\n询盘总金额: $" . number_format($totalValue, 2) . " USD\n";
echo "\n国家分布 (Top 5):\n";
foreach (array_slice($countries, 0, 5) as $country => $count) {
    echo "  {$country}: {$count} 条\n";
}
echo "\n转化状态:\n";
foreach ($statuses as $status => $count) {
    echo "  {$status}: {$count} 条\n";
}
