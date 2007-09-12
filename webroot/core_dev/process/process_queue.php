<?
	//this script is intended to be called regularry. every 30-60 seconds or so
	set_time_limit(60);

	require_once('config.php');

	$list = getProcessQueue($config['process']['process_limit']);

	foreach ($list as $job) {
		d($job);
		switch ($job['orderType'])
		{
			case PROCESSQUEUE_AUDIO_RECODE:
				//Recodes source audio file into orderParams destination format

				$file = $files->getFileInfo($job['resourceId']);
				if (!$file) {
					echo 'Error: no fileentry existed for resourceId '.$job['resourceId'];
					continue;
				}

				//fixme: kolla om filen finns p� disk innan vi forts�tter
				echo 'Recoding source audio of "'.$file['fileName'].'" ('.$file['fileMime'].') to format "'.$job['orderParams'].'" ...<br/>';

				switch ($job['orderParams']) {
					case 'ogg':
						//detta borde funka, men det g�r det inte. funkar om outifle har extension .ogg. -acodec ogg/vorbis ignoreras
						//$c = 'ffmpeg -i "'.$files->upload_dir.$job['resourceId'].'" outfile  -acodec ogg';

						//s� ist�llet tvingas vi g�ra det i 2 steg:
						$dst_file = 'tmpfile.ogg';
						$c = 'ffmpeg -i "'.$files->upload_dir.$job['resourceId'].'" '.$dst_file;
						break;
					case 'wma':
						$dst_file = 'tmpfile.wma';
						$c = 'ffmpeg -i "'.$files->upload_dir.$job['resourceId'].'" '.$dst_file;
						break;
					case 'mp3':
						//fixme: source & destination should not be able to be the same!
						$dst_file = 'tmpfile.mp3';
						$c = 'ffmpeg -i "'.$files->upload_dir.$job['resourceId'].'" '.$dst_file;
						break;
					default:
						die('unknown destination audio format: '.$job['orderParams']);
				}

				$exec_start = microtime(true);
				exec($c);
				$exec_end = microtime(true);
				echo 'Execution time: '.shortTimePeriod($exec_end - $exec_start).'<br/>';

				if (!file_exists($dst_file)) {
					echo '<b>FAILED - dst file '.$dst_file.' dont exist!<br/>';
					continue;
				}

				//skapa nytt tblFiles entry. l�nka det till orginal-filen
				$newId = $files->cloneEntry($job['resourceId']);

				//renama $dst_file till fileId f�r nya file entry
				rename($dst_file, $files->upload_dir.$newId);

				break;

			default:
				echo 'unknown ordertype: '.$job['orderType'].'<br/>';
				continue;
		}

		//marks queue item as completed
		$q = 'UPDATE tblProcessQueue SET orderCompleted=1 WHERE entryId='.$job['entryId'];
		$db->update($q);
	}

?>