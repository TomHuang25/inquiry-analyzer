<?php
/**
 * 询盘数据分析 - 核心分析引擎
 * 负责数据导入、多维度分析、指标计算
 */

class InquiryAnalyzer
{
    private array $records = [];
    private array $summary = [];

    /**
     * 从 CSV 文件导入数据
     */
    public function importCSV(string $filename): int
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("文件不存在: {$filename}");
        }

        $fp = fopen($filename, 'r');
        if (!$fp) {
            throw new RuntimeException("无法打开文件: {$filename}");
        }

        // 跳过 BOM
        $bom = fread($fp, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($fp);
        }

        $headers = fgetcsv($fp);
        if (!$headers) {
            throw new RuntimeException("CSV 文件为空或格式错误");
        }

        $columnMap = $this->detectColumns($headers);
        $this->records = [];
        $rowCount = 0;

        while (($row = fgetcsv($fp)) !== false) {
            if (count($row) < 3) continue;

            $record = [];
            foreach ($columnMap as $field => $index) {
                $record[$field] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            if (empty($record['timestamp']) && empty($record['email'])) continue;

            // 类型转换
            $record['quantity'] = (int)($record['quantity'] ?? 0);
            $record['estimated_value'] = (float)($record['estimated_value'] ?? 0);
            $record['response_hours'] = $record['response_hours'] !== '' ? (float)$record['response_hours'] : null;

            $this->records[] = $record;
            $rowCount++;
        }

        fclose($fp);
        $this->computeSummary();
        return $rowCount;
    }

    /**
     * 自动检测 CSV 列映射
     */
    private function detectColumns(array $headers): array
    {
        $map = [];
        $patterns = [
            'timestamp' => ['timestamp', '询盘时间', '时间', 'date', 'datetime', 'created_at', 'inquiry_date', '提交时间'],
            'company' => ['company', '公司名称', '公司', 'company_name', '企业名称'],
            'contact_name' => ['contact_name', '联系人', 'contact', 'name', '姓名', '客户名称'],
            'email' => ['email', '邮箱', 'e-mail', 'email_address', '电子邮箱'],
            'country' => ['country', '国家', 'country_name', '国家名称'],
            'country_code' => ['country_code', '国家代码', 'countrycode'],
            'product_interest' => ['product_interest', '感兴趣产品', 'product', '产品', '产品类别', 'product_category', 'inquiry_product'],
            'inquiry_type' => ['inquiry_type', '询盘类型', 'type', '类型', 'inquiry_category'],
            'source_channel' => ['source_channel', '来源渠道', 'channel', '渠道', 'source', '来源'],
            'source_type' => ['source_type', '来源类型', 'source_category', 'channel_type'],
            'quantity' => ['quantity', '数量', 'qty', 'order_qty'],
            'unit' => ['unit', '单位', 'qty_unit'],
            'estimated_value' => ['estimated_value_usd', '预估金额', 'estimated_value', 'value', '金额', 'amount', 'budget', 'pre估金额(USD)', '预估金额(USD)'],
            'conversion_status' => ['conversion_status', '转化状态', 'status', '状态', 'stage', '阶段', 'conversion_stage'],
            'response_hours' => ['response_time_hours', '回复时间', 'response_hours', '响应时间', 'response_time', '回复时间(小时)', 'response_time_hours'],
            'notes' => ['notes', '备注', 'remark', 'remarks', 'comment', 'comments', '说明'],
        ];

        foreach ($headers as $index => $header) {
            $normalized = mb_strtolower(trim($header));
            foreach ($patterns as $field => $matches) {
                if (in_array($normalized, $matches)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        // 默认映射
        $defaults = ['timestamp', 'company', 'contact_name', 'email', 'country', 'country_code',
                      'product_interest', 'inquiry_type', 'source_channel', 'source_type',
                      'quantity', 'unit', 'estimated_value', 'conversion_status', 'response_hours', 'notes'];
        foreach ($defaults as $i => $field) {
            if (!isset($map[$field]) && isset($headers[$i])) {
                $map[$field] = $i;
            }
        }

        return $map;
    }

    /**
     * 计算汇总统计
     */
    private function computeSummary(): void
    {
        $this->summary = [
            'total_inquiries' => count($this->records),
            'unique_companies' => count(array_unique(array_column($this->records, 'company'))),
            'unique_emails' => count(array_unique(array_column($this->records, 'email'))),
            'total_estimated_value' => array_sum(array_column($this->records, 'estimated_value')),
            'avg_estimated_value' => count($this->records) > 0 ? array_sum(array_column($this->records, 'estimated_value')) / count($this->records) : 0,
            'countries' => array_count_values(array_column($this->records, 'country')),
            'products' => array_count_values(array_column($this->records, 'product_interest')),
            'inquiry_types' => array_count_values(array_column($this->records, 'inquiry_type')),
            'source_channels' => array_count_values(array_column($this->records, 'source_channel')),
            'source_types' => array_count_values(array_column($this->records, 'source_type')),
            'conversion_statuses' => array_count_values(array_column($this->records, 'conversion_status')),
            'monthly_trend' => $this->computeMonthlyTrend(),
            'weekly_trend' => $this->computeWeeklyTrend(),
            'daily_trend' => $this->computeDailyTrend(),
            'hourly_distribution' => $this->computeHourlyDistribution(),
            'conversion_funnel' => $this->computeConversionFunnel(),
            'response_time_stats' => $this->computeResponseTimeStats(),
            'source_roi' => $this->computeSourceROI(),
            'product_value' => $this->computeProductValue(),
            'country_value' => $this->computeCountryValue(),
        ];
    }

    /**
     * 月度趋势
     */
    private function computeMonthlyTrend(): array
    {
        $trend = [];
        foreach ($this->records as $record) {
            $month = substr($record['timestamp'] ?? '', 0, 7);
            if (!isset($trend[$month])) {
                $trend[$month] = ['count' => 0, 'value' => 0];
            }
            $trend[$month]['count']++;
            $trend[$month]['value'] += $record['estimated_value'];
        }
        ksort($trend);
        return $trend;
    }

    /**
     * 周趋势
     */
    private function computeWeeklyTrend(): array
    {
        $trend = [];
        foreach ($this->records as $record) {
            if (empty($record['timestamp'])) continue;
            $week = date('Y-W', strtotime($record['timestamp']));
            if (!isset($trend[$week])) {
                $trend[$week] = ['count' => 0, 'value' => 0];
            }
            $trend[$week]['count']++;
            $trend[$week]['value'] += $record['estimated_value'];
        }
        ksort($trend);
        return $trend;
    }

    /**
     * 日趋势
     */
    private function computeDailyTrend(): array
    {
        $trend = [];
        foreach ($this->records as $record) {
            $date = substr($record['timestamp'] ?? '', 0, 10);
            if (!isset($trend[$date])) {
                $trend[$date] = ['count' => 0, 'value' => 0];
            }
            $trend[$date]['count']++;
            $trend[$date]['value'] += $record['estimated_value'];
        }
        ksort($trend);
        return $trend;
    }

    /**
     * 小时分布
     */
    private function computeHourlyDistribution(): array
    {
        $dist = array_fill(0, 24, 0);
        foreach ($this->records as $record) {
            if (!empty($record['timestamp'])) {
                $hour = (int)date('G', strtotime($record['timestamp']));
                $dist[$hour]++;
            }
        }
        return $dist;
    }

    /**
     * 转化漏斗
     */
    private function computeConversionFunnel(): array
    {
        // 定义漏斗阶段顺序
        $stageOrder = ['新询盘', '已回复', '报价中', '已报价', '样品阶段', '谈判中', '已下单', '已成交', '已关闭'];
        $funnel = [];

        foreach ($stageOrder as $stage) {
            $funnel[$stage] = 0;
        }

        foreach ($this->records as $record) {
            $status = $record['conversion_status'] ?? '新询盘';
            if (isset($funnel[$status])) {
                $funnel[$status]++;
            }
        }

        // 计算累计转化
        $cumulative = [];
        $total = count($this->records) ?: 1;
        $running = 0;
        foreach ($funnel as $stage => $count) {
            if ($stage !== '已关闭') {
                $running += $count;
            }
            $cumulative[$stage] = [
                'count' => $count,
                'percentage' => round($count / $total * 100, 1),
                'cumulative' => $running,
                'cumulative_pct' => round($running / $total * 100, 1),
            ];
        }

        return $cumulative;
    }

    /**
     * 响应时间统计
     */
    private function computeResponseTimeStats(): array
    {
        $times = array_filter(array_column($this->records, 'response_hours'), fn($v) => $v !== null);

        if (empty($times)) {
            return ['avg' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'count' => 0, 'distribution' => []];
        }

        sort($times);
        $count = count($times);

        // 响应时间分布
        $distribution = [
            '< 1小时' => 0,
            '1-4小时' => 0,
            '4-8小时' => 0,
            '8-24小时' => 0,
            '24-48小时' => 0,
            '> 48小时' => 0,
        ];

        foreach ($times as $t) {
            if ($t < 1) $distribution['< 1小时']++;
            elseif ($t < 4) $distribution['1-4小时']++;
            elseif ($t < 8) $distribution['4-8小时']++;
            elseif ($t < 24) $distribution['8-24小时']++;
            elseif ($t < 48) $distribution['24-48小时']++;
            else $distribution['> 48小时']++;
        }

        return [
            'avg' => round(array_sum($times) / $count, 1),
            'median' => round($times[(int)($count / 2)], 1),
            'min' => round(min($times), 1),
            'max' => round(max($times), 1),
            'count' => $count,
            'distribution' => $distribution,
        ];
    }

    /**
     * 来源渠道 ROI 分析
     */
    private function computeSourceROI(): array
    {
        $roi = [];
        foreach ($this->records as $record) {
            $channel = $record['source_channel'] ?? '未知';
            if (!isset($roi[$channel])) {
                $roi[$channel] = [
                    'channel' => $channel,
                    'type' => $record['source_type'] ?? '未知',
                    'inquiries' => 0,
                    'total_value' => 0,
                    'converted' => 0,
                ];
            }
            $roi[$channel]['inquiries']++;
            $roi[$channel]['total_value'] += $record['estimated_value'];
            if (in_array($record['conversion_status'] ?? '', ['已下单', '已成交'])) {
                $roi[$channel]['converted']++;
            }
        }

        // 计算转化率和平均价值
        foreach ($roi as &$item) {
            $item['conversion_rate'] = $item['inquiries'] > 0 ? round($item['converted'] / $item['inquiries'] * 100, 1) : 0;
            $item['avg_value'] = $item['inquiries'] > 0 ? round($item['total_value'] / $item['inquiries'], 2) : 0;
        }
        unset($item);

        // 按总价值排序
        uasort($roi, fn($a, $b) => $b['total_value'] <=> $a['total_value']);
        return $roi;
    }

    /**
     * 产品价值分析
     */
    private function computeProductValue(): array
    {
        $result = [];
        foreach ($this->records as $record) {
            $product = $record['product_interest'] ?? '未知';
            if (!isset($result[$product])) {
                $result[$product] = ['count' => 0, 'total_value' => 0, 'avg_value' => 0, 'converted' => 0];
            }
            $result[$product]['count']++;
            $result[$product]['total_value'] += $record['estimated_value'];
            if (in_array($record['conversion_status'] ?? '', ['已下单', '已成交'])) {
                $result[$product]['converted']++;
            }
        }
        foreach ($result as &$item) {
            $item['avg_value'] = $item['count'] > 0 ? round($item['total_value'] / $item['count'], 2) : 0;
            $item['conversion_rate'] = $item['count'] > 0 ? round($item['converted'] / $item['count'] * 100, 1) : 0;
        }
        unset($item);
        uasort($result, fn($a, $b) => $b['total_value'] <=> $a['total_value']);
        return $result;
    }

    /**
     * 国家价值分析
     */
    private function computeCountryValue(): array
    {
        $result = [];
        foreach ($this->records as $record) {
            $country = $record['country'] ?? '未知';
            if (!isset($result[$country])) {
                $result[$country] = ['count' => 0, 'total_value' => 0, 'avg_value' => 0, 'converted' => 0];
            }
            $result[$country]['count']++;
            $result[$country]['total_value'] += $record['estimated_value'];
            if (in_array($record['conversion_status'] ?? '', ['已下单', '已成交'])) {
                $result[$country]['converted']++;
            }
        }
        foreach ($result as &$item) {
            $item['avg_value'] = $item['count'] > 0 ? round($item['total_value'] / $item['count'], 2) : 0;
            $item['conversion_rate'] = $item['count'] > 0 ? round($item['converted'] / $item['count'] * 100, 1) : 0;
        }
        unset($item);
        uasort($result, fn($a, $b) => $b['total_value'] <=> $a['total_value']);
        return $result;
    }

    /**
     * 获取处理后的记录
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * 获取摘要统计
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * 按条件过滤记录
     */
    public function filter(string $country = '', string $sourceType = '', string $product = '', string $status = '', string $dateFrom = '', string $dateTo = ''): array
    {
        return array_filter($this->records, function ($record) use ($country, $sourceType, $product, $status, $dateFrom, $dateTo) {
            if ($country && ($record['country'] ?? '') !== $country) return false;
            if ($sourceType && ($record['source_type'] ?? '') !== $sourceType) return false;
            if ($product && ($record['product_interest'] ?? '') !== $product) return false;
            if ($status && ($record['conversion_status'] ?? '') !== $status) return false;
            if ($dateFrom && ($record['timestamp'] ?? '') < $dateFrom) return false;
            if ($dateTo && ($record['timestamp'] ?? '') > $dateTo . ' 23:59:59') return false;
            return true;
        });
    }

    /**
     * 导出为 CSV
     */
    public function exportCSV(string $filename, array $records = []): string
    {
        $records = $records ?: $this->records;
        $fp = fopen($filename, 'w');
        fwrite($fp, "\xEF\xBB\xBF");

        fputcsv($fp, ['询盘时间', '公司名称', '联系人', '邮箱', '国家', '感兴趣产品', '询盘类型', '来源渠道', '来源类型', '数量', '单位', '预估金额(USD)', '转化状态', '回复时间(小时)']);

        foreach ($records as $record) {
            fputcsv($fp, [
                $record['timestamp'] ?? '',
                $record['company'] ?? '',
                $record['contact_name'] ?? '',
                $record['email'] ?? '',
                $record['country'] ?? '',
                $record['product_interest'] ?? '',
                $record['inquiry_type'] ?? '',
                $record['source_channel'] ?? '',
                $record['source_type'] ?? '',
                $record['quantity'] ?? '',
                $record['unit'] ?? '',
                $record['estimated_value'] ?? '',
                $record['conversion_status'] ?? '',
                $record['response_hours'] ?? '',
            ]);
        }

        fclose($fp);
        return $filename;
    }
}
