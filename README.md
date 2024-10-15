To implement Latta into raw PHP do:

1. Install Latta Recorder via Composer

```
composer require lattaai/latta-php-recorder
```

2. Inject Into All PHP Files (you can create latta.php file and inject file)

```
use LattaAi\Recorder\LattaRecorder;

$recorder = new LattaRecorder("API KEY");
$recorder->startRecording("PHP", phpversion(), PHP_OS, "en", "server");
```