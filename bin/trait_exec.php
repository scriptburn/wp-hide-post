<?php
/**
 * Class SampleTest
 *
 * @package Wp_Hide_Post
 */

/**
 * Sample test case.
 */

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait SCBExec
{
	protected $mysql_dump_bin, $mysql_bin;
	private function exec($processes, $errorMessage = "")
	{
		$output = [];
		$processes = is_array($processes) && isset($processes[0]) ? $processes : [$processes];

		try
		{
			foreach ($processes as $key => $command)
			{
				$cmd = is_array($command) ? @$command['cmd'] : $command;
				$options = is_array($command) ? $command : [];
				unset($options['cmd']);
				if (!$cmd)
				{
					throw new \Exception("invalid cmd");
				}

				$process_options = array_merge(['cwd' => realpath(__DIR__."/../"), 'env' => null, 'input' => null, 'options' => []], $options);
				$process = new Process($cmd, @$process_options['cwd'], @$process_options['env'], @$process_options['input'], @$process_options['options']);


				$process->mustRun();

				//$process->wait();

				$output[$cmd] = $process->getOutput();
			}
			if (count($output) > 1)
			{
				return [1, ($output)];
			}
			else
			{
				$output = array_values($output);

				return [1, implode("\n", $output)];
			}
		}
		catch (ProcessFailedException $e)
		{
			if ($errorMessage)
			{
				throw new \Exception($errorMessage);
			}
			else
			{
				if (count($output) > 1)
				{
					return [0, ($output)];
				}
				else
				{
					return [0, $e->getMessage()];
				}
			}
		}
	}
	public function copy_current_db_to($to_db, $to_user = null, $to_pass = null)
	{
		$to_user = is_null($to_user) || empty($to_user) ? DB_USER : $to_user;
		$to_pass = is_null($to_pass) || empty($to_pass) ? DB_USER : $to_pass;

		$ret = $this->exec(['cmd' => $this->mysql_dump_bin."  ".DB_NAME." -u ".DB_USER." -p".DB_PASSWORD.">".DB_NAME.".sql"]);
		if (!$ret[0])
		{
			return $ret[1];
		}
		else
		{
			$ret = $this->exec(['cmd' => $this->mysql_bin."  ".$to_db." -u ".$to_user." -p".$to_pass."<".DB_NAME.".sql"]);
			if (!$ret[0])
			{
				return $ret[1];
			}
			else
			{
				return true;
			}
		}
	}
	public function setPluginStatus($name, $status)
	{
		$ret = $this->exec(['cmd' => 'wp plugin '.$status.' '.$name]);
	//	print_r("$name, $status \n");
		//print_r($ret);	
		if (!$ret[0])
		{
			return $ret[1];
		}
		else
		{
			$status = $status.'d';
			$matches = array();
			preg_match_all('/success:(.+?)('.$status.')(.+?)/im', $ret[1], $matches, PREG_SET_ORDER, 0);

			return strtolower(@$matches[0][2]) == $status;
		}
	}
}
