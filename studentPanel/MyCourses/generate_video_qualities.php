<?php
require_once '../../Configurations/config.php';

// Ensure only authorized users can generate qualities
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

// Path to FFmpeg
$ffmpeg_path = 'C:/ffmpeg/bin/ffmpeg.exe';

// Validate and sanitize input
$video_path = $_POST['video_path'] ?? '';
$video_path = realpath('../../uploads/course_uploads/course_videos/' . basename($video_path));

if (!$video_path || !file_exists($video_path)) {
    die(json_encode(['error' => 'Invalid video path']));
}

// Output directory for quality variants
$output_dir = dirname($video_path) . '/qualities/';
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0777, true);
}

// Qualities to generate
$qualities = [
    ['name' => '360p', 'resolution' => '640x360', 'bitrate' => '500k'],
    ['name' => '480p', 'resolution' => '854x480', 'bitrate' => '1000k'],
    ['name' => '720p', 'resolution' => '1280x720', 'bitrate' => '2500k']
];

$output_files = [];

foreach ($qualities as $quality) {
    $output_file = $output_dir . pathinfo($video_path, PATHINFO_FILENAME) . 
                   '_' . $quality['name'] . '.' . pathinfo($video_path, PATHINFO_EXTENSION);
    
    $cmd = "\"{$ffmpeg_path}\" -i \"{$video_path}\" " .
           "-vf scale={$quality['resolution']} " .
           "-b:v {$quality['bitrate']} " .
           "-c:a copy " .
           "\"{$output_file}\"";
    
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0) {
        $output_files[] = [
            'name' => $quality['name'],
            'src' => str_replace('../../', '', $output_file)
        ];
    }
}

echo json_encode(['success' => true, 'qualities' => $output_files]);
?>
