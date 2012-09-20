<?
abstract class CBPRuntimeService
{
	protected $runtime;

	public function SetRuntime(CBPRuntime $runtime)
	{
		$this->$runtime = $runtime;
	}

	public function Start()
	{
		
	}

	public function Stop()
	{
		
	}
}
?>