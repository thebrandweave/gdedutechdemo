<?php
// Specify the FFmpeg path (adjust as needed)
$ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe'; // Or full path like 'C:\\path\\to\\ffmpeg\\ffmpeg.exe'

// Test FFmpeg version
try {
    $version = shell_exec($ffmpegPath . ' -version');
    
    if ($version) {
        echo "FFmpeg is working correctly!<br>";
        echo "<pre>$version</pre>";
    } else {
        echo "Failed to execute FFmpeg. Check your path and permissions.";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

// Optional: Simple video conversion example function
function convertVideo($inputFile, $outputFile) {
    global $ffmpegPath;
    $command = escapeshellcmd("{$ffmpegPath} -i " . escapeshellarg($inputFile) . " " . escapeshellarg($outputFile));
    exec($command, $output, $returnVar);
    
    return $returnVar == 0;
}
exec('ffmpeg -version', $output, $returnCode);
if ($returnCode === 0) {
    echo "FFmpeg is available";
} else {
    echo "FFmpeg is not available";
}
?>