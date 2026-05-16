<?php
// Configuration
$ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe';  // Assuming it's in system PATH
$uploadDir = __DIR__ . '/uploads/course_uploads/course_videos/';  // Use absolute path with forward slashes
$processedDir = __DIR__ . '/processed/';  // Use absolute path with forward slashes

// Ensure directories exist
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($processedDir)) mkdir($processedDir, 0777, true);

// Video Processing Functions
class VideoProcessor {
    private $ffmpegPath;

    public function __construct($ffmpegPath) {
        $this->ffmpegPath = $ffmpegPath;
    }

    // Convert video to different format
    public function convertFormat($inputFile, $outputFile, $targetFormat = 'mp4') {
        $command = escapeshellcmd("{$this->ffmpegPath} -i " . escapeshellarg($inputFile) . 
                                  " -c:v libx264 -preset medium " . 
                                  escapeshellarg($outputFile));
        exec($command, $output, $returnVar);
        return $returnVar == 0;
    }

    // Resize video
    public function resizeVideo($inputFile, $outputFile, $width = 640, $height = 480) {
        $command = escapeshellcmd("{$this->ffmpegPath} -i " . escapeshellarg($inputFile) . 
                                  " -vf scale={$width}:{$height} " . 
                                  escapeshellarg($outputFile));
        exec($command, $output, $returnVar);
        return $returnVar == 0;
    }

    // Extract thumbnail
    public function extractThumbnail($inputFile, $outputFile, $time = '00:00:01') {
        $command = escapeshellcmd("{$this->ffmpegPath} -i " . escapeshellarg($inputFile) . 
                                  " -ss {$time} -vframes 1 " . 
                                  escapeshellarg($outputFile));
        exec($command, $output, $returnVar);
        return $returnVar == 0;
    }
}

// Initialize Processor
$processor = new VideoProcessor($ffmpegPath);

// Example Usage
$sampleVideo = $uploadDir . '674aa92bb7586_Golden Glitter sparkling background video _ golden particles background hd _ Royalty Free Footage.mp4';

// Debug information
echo "Looking for video at: " . $sampleVideo . "<br>";
echo "File exists: " . (file_exists($sampleVideo) ? "Yes" : "No") . "<br>";

// Perform video processing
if (file_exists($sampleVideo)) {
    echo "Processing video...<br>";

    // Convert format
    if ($processor->convertFormat($sampleVideo, $processedVideo)) {
        echo "Video converted successfully!<br>";
    }

    // Extract thumbnail
    if ($processor->extractThumbnail($sampleVideo, $thumbnailFile)) {
        echo "Thumbnail extracted successfully!<br>";
    }

    // Resize video
    $resizedVideo = $processedDir . 'resized_video.mp4';
    if ($processor->resizeVideo($sampleVideo, $resizedVideo, 320, 240)) {
        echo "Video resized successfully!<br>";
    }
} else {
    echo "Sample video not found!<br>";
    echo "Please ensure the video file exists in the following directory:<br>";
    echo htmlspecialchars($uploadDir);
}
?>