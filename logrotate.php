<?php

date_default_timezone_set('Asia/Jakarta');
// error_reporting(E_ALL & ~E_USER_NOTICE);
error_reporting(E_ALL);
// ini_set('display_errors', 1);

$file = 'etc/logrotate.d/01-nmon.ini';
$data = parse_ini_file($file);

$minAge = (isset($data['minAge']) ? strtotime($data['minAge']) : FALSE);
$compress = (isset($data['compress']) ? $data['compress'] : FALSE);
$archive = (isset($data['archive']) ? $data['archive'] : FALSE);
$archiveBaseDir = (isset($data['archiveBaseDir']) ? $data['archiveBaseDir'] : FALSE);

foreach (glob($data['path']) as $file) {
  trigger_error($file);
  $fileTime = filemtime($file);
  if ($minAge) {
    if ($fileTime > $minAge) {
      continue;
    }
  }
  if ($compress) {
    if (is_file($file.'.bz2')) {
	  trigger_error('compressed file exists: '.$file.'.bz2', E_USER_WARNING);
	  continue;
	}
    $command = '/usr/bin/env bzip2 -9 '.$file;
    trigger_error($command);
    passthru($command, $exitCode);
	if (empty($exitCode) == FALSE) {
	  trigger_error('compress error');
	  continue;
	}
	$file = $file.'.bz2';
  }
  if (empty($archive) == FALSE) {
    if (empty($archiveBaseDir)) {
	  $archiveBaseDir = dirname($file);
	}
	$archiveDir = $archiveBaseDir;
	if (is_dir($archiveDir) == FALSE) { mkdir($archiveDir); }
	$archiveDir .= '/archives';
	if (is_dir($archiveDir) == FALSE) { mkdir($archiveDir); }
	$archiveDir .= '/'.date('Y', $fileTime);
	if (is_dir($archiveDir) == FALSE) { mkdir($archiveDir); }
	$archiveDir .= '/'.date('Y-m', $fileTime);
	if (is_dir($archiveDir) == FALSE) { mkdir($archiveDir); }
	$archiveFile = $archiveDir.'/'.basename($file);
	if (is_file($archiveFile)) {
	  trigger_error('archiveFile already exists');
	} else {
	  trigger_error('archive to '.$archiveFile);
	  rename($file, $archiveFile);
	}
  }
}

