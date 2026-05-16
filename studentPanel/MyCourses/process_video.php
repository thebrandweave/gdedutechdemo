<?php
// Add FFmpeg path constant at the top
define('FFMPEG_PATH', 'C:\ffmpeg\bin\ffmpeg.exe'); // Adjust this path to match your system

function processVideo($videoPath, $videoId) {
    $outputDir = "../../uploads/course_uploads/course_videos/{$videoId}";
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    // Use FFMPEG_PATH constant
    $command = FFMPEG_PATH . " -i {$videoPath} \
        -filter_complex \"
            [0:v]split=4[v1][v2][v3][v4]; \
            [v1]scale=w=1920:h=1080[v1out]; \
            [v2]scale=w=1280:h=720[v2out]; \
            [v3]scale=w=854:h=480[v3out]; \
            [v4]scale=w=640:h=360[v4out]
        \" \
        -map \"[v1out]\" -map 0:a -c:v:0 libx264 -b:v:0 5000k -maxrate:v:0 5350k -bufsize:v:0 7000k \
        -map \"[v2out]\" -map 0:a -c:v:1 libx264 -b:v:1 2800k -maxrate:v:1 3000k -bufsize:v:1 4000k \
        -map \"[v3out]\" -map 0:a -c:v:2 libx264 -b:v:2 1400k -maxrate:v:2 1498k -bufsize:v:2 2100k \
        -map \"[v4out]\" -map 0:a -c:v:3 libx264 -b:v:3 800k -maxrate:v:3 856k -bufsize:v:3 1200k \
        -c:a aac -b:a 128k \
        -var_stream_map \"v:0,a:0,name:1080p v:1,a:1,name:720p v:2,a:2,name:480p v:3,a:3,name:360p\" \
        -master_pl_name master.m3u8 \
        -f hls \
        -hls_time 6 \
        -hls_list_size 0 \
        -hls_segment_filename \"{$outputDir}/%v/segment%d.ts\" \
        -hls_playlist_type vod \
        {$outputDir}/%v/playlist.m3u8";

    exec($command, $output, $returnCode);
    if ($returnCode !== 0) {
        error_log("FFmpeg processVideo error: " . implode("\n", $output));
    }
    return $returnCode === 0;
}

function generateVideoQualities($inputFile, $filename) {
    $qualities = [
        '1080' => '-vf "scale=-2:1080"',
        '720' => '-vf "scale=-2:720"',
        '480' => '-vf "scale=-2:480"',
        '360' => '-vf "scale=-2:360"'
    ];
    
    $baseOutputPath = dirname($inputFile) . '/';
    $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
    
    foreach ($qualities as $quality => $scaleFilter) {
        $outputFile = $baseOutputPath . $filenameWithoutExt . '_' . $quality . 'p.mp4';
        // Use FFMPEG_PATH constant
        $command = FFMPEG_PATH . " -i " . escapeshellarg($inputFile) . " " . 
                  $scaleFilter . " -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k " . 
                  escapeshellarg($outputFile) . " 2>&1";
        
        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            error_log("FFmpeg generateVideoQualities error for quality {$quality}p: " . implode("\n", $output));
        } else {
            error_log("Successfully generated {$quality}p version: $outputFile");
        }
    }
}

// Add a test function
function testFFmpeg() {
    $command = FFMPEG_PATH . " -version";
    exec($command, $output, $returnCode);
    if ($returnCode === 0) {
        error_log("FFmpeg is working: " . implode("\n", $output));
        return true;
    } else {
        error_log("FFmpeg test failed: " . implode("\n", $output));
        return false;
    }
}