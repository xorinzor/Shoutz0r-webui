<?php

namespace App\HealthCheck;

use JetBrains\PhpStorm\Pure;

class WritableDirsHealthCheck extends BaseHealthCheck {

    private array $errors;
    private array $directories;

    #[Pure]
    public function __construct(array $directories) {
        parent::__construct(
            'Writable directories',
            'Checks if all configured directories are writable',
            'All directories are writable'
        );

        $this->directories  = $directories;
        $this->isHealthy    = false;
        $this->errors       = [];
    }

    #[Pure]
    public function getStatus(): string {
        if($this->isHealthy()) {
            return $this->status;
        } else {
            return "The following errors require a manual fix:\n" . implode("\n", $this->errors);
        }
    }

    public function checkHealth(): void {
        $healthCheck = true;
        $caughtErrors = [];

        # Check if all symlinks exist
        foreach($this->directories as $directory) {
            clearstatcache(false, $directory);
            if(is_writable($directory) === false) {
                $healthCheck = false;
                $caughtErrors[] = "Not writable: $directory";
                continue;
            }
        }

        $this->isHealthy = $healthCheck;
        $this->errors = $caughtErrors;
    }

    public function fix(): HealthCheckFixResult {
        // Not quite sure how to fix this by code. IMO a sysadmin should do this manually.
        $result = new HealthCheckFixResult();

        if($this->isHealthy) {
            $result->setFixed(true);
            $result->setMessage('No fix required');
        } else {
            $result->setFixed(false);
            $result->setMessage('No automatic fix available, manual fix required.');
        }

        return $result;
    }
}