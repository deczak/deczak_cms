<?php

require_once 'CSingleton.php';

/**
 * 	This class handles system modules or regular modules with system functions to inject processes into various sections
 * 
 * 	This is a singleton class.
 * 
 * 	eg:
 * 	cmsSystemModules::instance() -> register(cmsSystemModules::SECTION_TOOLBAR, 'testItBro');
 * 	cmsSystemModules::instance() -> call(cmsSystemModules::SECTION_TOOLBAR);
 */

class cmsSystemModulesFunction
{
	public int $section;
	public $systemFunction;
	public function
	__construct(
		int $section,
		callable $systemFunction		
	)
	{
		$this -> section 		= $section;
		$this -> systemFunction = $systemFunction;
	}

	public function
	isSection(
		int $section
	) : bool
	{
		return $this -> section === $section;
	}

	public function
	call(
		array $parameters = []
	) : void
	{
		($this -> systemFunction)($parameters);
	}
}

class cmsSystemModules extends CSingleton implements IteratorAggregate 
{
	public const SECTION_TOOLBAR 	 = 1;
	public const SECTION_PAGEHEAD 	 = 2;
	public const SECTION_SYSTEM_INIT = 3;

	/*
	 *	Register a Module with his system function on defined section
	 */
	public function
	register(
		int $section,
		callable $systemFunction
	) : void
	{
		if(!property_exists($this, 'systemFunctionList'))
		{
			$this -> systemFunctionList = [];
		}

		$this -> systemFunctionList[] = new cmsSystemModulesFunction($section, $systemFunction);

	}

    public function
	getIterator() : Traversable
	{
		if(!property_exists($this, 'systemFunctionList'))
		{
			$this -> systemFunctionList = [];
		}
        return new ArrayIterator($this -> systemFunctionList);
    }

	public function
	call(
		int $section,
		array $parameters = []
	) : void
	{
		foreach($this as $systemModuleFunction)
		{
			if($systemModuleFunction -> isSection($section))
				$systemModuleFunction -> call($parameters);
		}
	}
}
