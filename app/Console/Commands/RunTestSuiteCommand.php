<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Testing\Services\TestSuiteService;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class RunTestSuiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:suite
                            {--type=all : Type of tests to run (all, unit, feature, integration, browser, performance, security)}
                            {--coverage : Generate code coverage report}
                            {--quality : Run code quality checks}
                            {--report : Generate detailed test report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive test suite with coverage and quality analysis';

    protected TestSuiteService $testSuite;

    public function __construct(TestSuiteService $testSuite)
    {
        parent::__construct();
        $this->testSuite = $testSuite;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Starting Test Suite Execution...');
        $this->newLine();

        $startTime = microtime(true);
        $testType = $this->option('type');

        try {
            $results = [];

            // Run tests based on type
            switch ($testType) {
                case 'all':
                    $this->info('🚀 Running complete test suite...');
                    $results = $this->testSuite->runCompleteTestSuite();
                    break;

                case 'unit':
                    $this->info('🔧 Running unit tests...');
                    $results['unit_tests'] = $this->testSuite->runUnitTests();
                    break;

                case 'feature':
                    $this->info('⚙️ Running feature tests...');
                    $results['feature_tests'] = $this->testSuite->runFeatureTests();
                    break;

                case 'integration':
                    $this->info('🔗 Running integration tests...');
                    $results['integration_tests'] = $this->testSuite->runIntegrationTests();
                    break;

                case 'browser':
                    $this->info('🌐 Running browser tests...');
                    $results['browser_tests'] = $this->testSuite->runBrowserTests();
                    break;

                case 'performance':
                    $this->info('⚡ Running performance tests...');
                    $results['performance_tests'] = $this->testSuite->runPerformanceTests();
                    break;

                case 'security':
                    $this->info('🔒 Running security tests...');
                    $results['security_tests'] = $this->testSuite->runSecurityTests();
                    break;

                default:
                    $this->error("Invalid test type: {$testType}");
                    return SymfonyCommand::FAILURE;
            }

            // Generate coverage if requested
            if ($this->option('coverage')) {
                $this->info('📊 Generating code coverage report...');
                $results['code_coverage'] = $this->testSuite->generateCodeCoverage();
            }

            // Run quality checks if requested
            if ($this->option('quality')) {
                $this->info('⭐ Running code quality checks...');
                $results['code_quality'] = $this->testSuite->runCodeQualityChecks();
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->newLine();
            $this->info("✅ Test execution completed in {$duration}ms");

            // Display results summary
            $this->displayResults($results);

            // Generate detailed report if requested
            if ($this->option('report')) {
                $this->generateDetailedReport($results);
            }

            return SymfonyCommand::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test execution failed: ' . $e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return SymfonyCommand::FAILURE;
        }
    }

    /**
     * Display test results summary
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📋 Test Results Summary:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Test suites results
        $testSuites = ['unit_tests', 'feature_tests', 'integration_tests', 'browser_tests', 'performance_tests', 'security_tests'];

        foreach ($testSuites as $suite) {
            if (isset($results[$suite])) {
                $status = $results[$suite]['status'] ?? 'unknown';
                $duration = isset($results[$suite]['duration']) ? number_format($results[$suite]['duration'], 2) . 's' : 'N/A';

                $statusIcon = match($status) {
                    'passed' => '✅',
                    'failed' => '❌',
                    'error' => '⚠️',
                    default => '❓'
                };

                $suiteName = ucwords(str_replace('_', ' ', $suite));
                $this->line("   {$statusIcon} {$suiteName}: {$status} ({$duration})");
            }
        }

        // Coverage results
        if (isset($results['code_coverage'])) {
            $coverage = $results['code_coverage'];
            $coveragePercent = $coverage['coverage_percentage'] ?? 0;
            $coverageIcon = $coveragePercent >= 80 ? '✅' : ($coveragePercent >= 60 ? '⚠️' : '❌');

            $this->line("   {$coverageIcon} Code Coverage: {$coveragePercent}%");

            if (isset($coverage['lines_covered'], $coverage['lines_total'])) {
                $this->line("      Lines: {$coverage['lines_covered']}/{$coverage['lines_total']}");
            }
        }

        // Quality results
        if (isset($results['code_quality'])) {
            $quality = $results['code_quality'];
            $qualityScore = $quality['overall_score'] ?? 0;
            $qualityIcon = $qualityScore >= 90 ? '✅' : ($qualityScore >= 70 ? '⚠️' : '❌');

            $this->line("   {$qualityIcon} Code Quality: {$qualityScore}/100");

            foreach (['phpstan', 'php_cs_fixer', 'phpmd', 'phpcs'] as $tool) {
                if (isset($quality[$tool])) {
                    $toolStatus = $quality[$tool]['status'] ?? 'unknown';
                    $toolIcon = $toolStatus === 'passed' ? '✅' : ($toolStatus === 'not_available' ? '❓' : '❌');
                    $this->line("      {$toolIcon} " . strtoupper($tool) . ": {$toolStatus}");
                }
            }
        }

        // Overall summary
        if (isset($results['summary'])) {
            $summary = $results['summary'];
            $this->newLine();
            $this->info('🎯 Overall Summary:');
            $this->line("   Total Test Suites: {$summary['total_test_suites']}");
            $this->line("   Passed: {$summary['passed_test_suites']}");
            $this->line("   Failed: {$summary['failed_test_suites']}");
            $this->line("   Success Rate: {$summary['success_rate']}%");

            $overallIcon = $summary['overall_status'] === 'passed' ? '✅' : '❌';
            $this->line("   {$overallIcon} Overall Status: " . strtoupper($summary['overall_status']));
        }
    }

    /**
     * Generate detailed report
     */
    private function generateDetailedReport(array $results): void
    {
        $this->newLine();
        $this->info('📄 Generating detailed test report...');

        $reportPath = storage_path('app/testing/detailed_report_' . date('Y-m-d_H-i-s') . '.json');

        if (!is_dir(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0755, true);
        }

        $report = [
            'generated_at' => now()->toISOString(),
            'command_options' => [
                'type' => $this->option('type'),
                'coverage' => $this->option('coverage'),
                'quality' => $this->option('quality'),
            ],
            'results' => $results,
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'os' => PHP_OS,
            ],
        ];

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->line("   📁 Report saved to: {$reportPath}");
    }
}
