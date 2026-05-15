<?php
/**
 * 询盘数据分析 - 报告生成器
 */

class InquiryReportGenerator
{
    private array $records;
    private array $summary;
    private string $title;

    public function __construct(array $records, array $summary, string $title = '询盘数据分析报告')
    {
        $this->records = $records;
        $this->summary = $summary;
        $this->title = $title;
    }

    public function generateHTML(): string
    {
        ob_start();
        $records = $this->records;
        $summary = $this->summary;
        $title = $this->title;
        $generatedAt = date('Y-m-d H:i:s');
        include __DIR__ . '/report-template.php';
        return ob_get_clean();
    }

    public function saveToFile(string $filename): string
    {
        file_put_contents($filename, $this->generateHTML());
        return $filename;
    }

    public static function formatMoney(float $amount): string
    {
        if ($amount >= 1000000) return '$' . round($amount / 1000000, 1) . 'M';
        if ($amount >= 1000) return '$' . round($amount / 1000, 1) . 'K';
        return '$' . number_format($amount, 2);
    }

    public static function formatNumber(int $number): string
    {
        return number_format($number, 0, '.', ',');
    }

    public static function topN(array $data, int $n = 10): array
    {
        arsort($data);
        return array_slice($data, 0, $n, true);
    }
}
