<?php

require_once 'CSingleton.php';

class CBenchmark extends CSingleton
{

	private $m_measurement;
	private $m_benchmarkActive;

	public function
	initialize(bool $_activateBenchmark = false)
	{
		$this -> m_measurement = [];
		$this -> m_benchmarkActive = $_activateBenchmark;
	}
	
	public function
	measurementPoint(string $_description)
	{
	
		$this -> m_measurement[] = [ $this -> getMicrotime() , $_description ];
	
	}
	
	public function
	stop()
	{
		if(!$this -> m_benchmarkActive || empty($this -> m_measurement))
			return;
		
		$this -> measurementPoint('');
				
		$end = $this -> getMicrotime();
		
		echo '<div style="background:#cc0000; color:#fff; padding:6px 10px; font-size:0.8em; letter-spacing:0.06em;">';
				
		if(count($this -> m_measurement) > 1)
			echo 'Benchmark start<br>';
		
		for($i = 1; $i < count($this -> m_measurement); $i++)
		{		
			echo '&nbsp;&nbsp;... '. number_format(($this -> m_measurement[$i][0] - $this -> m_measurement[$i - 1][0]),6) .' seconds for '. $this -> m_measurement[$i - 1][1] .'<br>';
		}
				
		
		echo 'Total execution time took '. number_format(($end - $this -> m_measurement[0][0]),6) .' seconds';
				
		echo '</div>';
	}
	
	private function
	getMicrotime()
	{
		return microtime(true);
	}
		
}

?>
