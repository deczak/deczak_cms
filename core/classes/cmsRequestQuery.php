<?php

class QueryValidation
{
    const STRIP_TAGS 	= 0x01;
    const TRIM 			= 0x02;
    const IS_DIGIT 		= 0x04;
    const IS_EMPTY 		= 0x08;
    const IS_NOTEMPTY 	= 0x10;
	const CAST_INTEGER	= 0x20;
	const CAST_BOOL 	= 0x40;
    const IS_EMAIL 		= 0x80;
    const LOWERCASE		= 0x100;
    const UPPERCASE		= 0x200;
}

class cmsRequestQueryProcessor
{
	private $qryKey;
	private $qrySource;
	private $qryDefault;
	private $qryKeyOut;
	private $qryValue;
	private $qryValidate;

	public function
	__construct()
	{
		$this->qryKey 		= null;
		$this->qrySource 	= null;
		$this->qryDefault 	= null;
		$this->qryKeyOut 	= null;
		$this->qryValue 	= null;
		$this->qryValidate 	= null;
	}

	public function
	post(string $key) : cmsRequestQueryProcessor
	{
		// todo speichern
		$this->qryKey 		= $key;
		$this->qrySource 	= 'post';
		return $this;
	}

	public function
	get(string $key) : cmsRequestQueryProcessor
	{
		// todo speichern
		$this->qryKey 		= $key;
		$this->qrySource 	= 'get';
		return $this;
	}

	public function
	data(string $key, array|object &$storage) : cmsRequestQueryProcessor
	{
		// todo speichern
		$this->qryKey 		= $key;
		$this->qrySource 	= $storage;
		return $this;
	}

	public function
	default($default)
	{
		$this->qryDefault 	= $default;
		return $this;
	}

	public function
	out(string $keyOut)
	{
		$this->qryKeyOut 	= $keyOut;
		return $this;
	}

	public function 
	validate($validateFlags)
	{
		if(is_object($validateFlags))
		$this->qryValidate = $validateFlags;
		$this->qryValidate = $validateFlags;
		return $this;
	}

	public function
	exec()
	{
		$qryValue = null;

		if(is_string($this->qrySource))
		{
			switch($this->qrySource)
			{
				case 'post': 	$qryValue = $this->_validate($_POST, $this->qryKey); 	break;
				case 'get': 	$qryValue = $this->_validate($_GET, $this->qryKey); 	break;
			}
		}
		else
		{
			$qryValue = $this->_validate($this->qrySource, $this->qryKey);
		}

		$this->qryValue = $qryValue;
		return $qryValue;
	}

	public function
	getValue()
	{
		return $this->qryValue;
	}

	public function
	getKey() : ?string
	{
		if($this->qryKeyOut !== null)
			return $this->qryKeyOut;
		return $this->qryKey;
	}

	private function
	_validate(&$source, $key)
	{
		if(is_array($source) && !isset($source[$key]))
			return $this->qryDefault;

		if(is_object($source) && !property_exists($source, $key))
			return $this->qryDefault;

		if(is_array($source))
			$toValidate = $source[$key];

		if(is_object($source))
			$toValidate = $source->$key;

		if($this->qryValidate !== null)
		{
			// Formating Validation

			if ($this->qryValidate & QueryValidation::STRIP_TAGS)
			{
				$toValidate = strip_tags($toValidate);
			}

			if ($this->qryValidate & QueryValidation::TRIM)
			{
				$toValidate = trim($toValidate);
			}

			if ($this->qryValidate & QueryValidation::LOWERCASE)
			{
				$toValidate = strtolower($toValidate);
			}

			if ($this->qryValidate & QueryValidation::UPPERCASE)
			{
				$toValidate = strtoupper($toValidate);
			}

			// CAST Validation

			if ($this->qryValidate & QueryValidation::CAST_INTEGER)
			{
				$toValidate = (int)$toValidate;
			}

			if ($this->qryValidate & QueryValidation::CAST_BOOL)
			{
				$toValidate = filter_var($toValidate, FILTER_VALIDATE_BOOLEAN);
			}

			// IS Validation

			if ($this->qryValidate & QueryValidation::IS_EMAIL)
			{
				if(filter_var($toValidate, FILTER_VALIDATE_EMAIL) === false) 
					return $this->qryDefault;
			}

			if ($this->qryValidate & QueryValidation::IS_DIGIT)
			{
				if(!ctype_digit($toValidate)) 
					return $this->qryDefault;
			}

			if ($this->qryValidate & QueryValidation::IS_EMPTY)
			{
				if(!empty($toValidate)) 
					return $this->qryDefault;
			}

			if ($this->qryValidate & QueryValidation::IS_NOTEMPTY)
			{
				if(empty($toValidate)) 
					return $this->qryDefault;
			}
		}

		return $toValidate;
	}
}

class cmsRequestQuery
{
	private cmsRequestQueryProcessor $localProcessor;
	private array $collectedProcessors;
	private bool $collectMode;
	
	public function
	__construct(bool $collectMode = false)
	{
		$this->localProcessor 		= new cmsRequestQueryProcessor;
		$this->collectedProcessors 	= [];
		$this->collectMode 			= $collectMode;
	}

	public static function
	prc() : cmsRequestQueryProcessor
	{
		$processor = new cmsRequestQueryProcessor;
		return $processor;
	}

	public function
	post(string $key) : cmsRequestQuery
	{
		$this->localProcessor->post($key);
		return $this;
	}

	public function
	get(string $key) : cmsRequestQuery
	{
		$this->localProcessor->get($key);
		return $this;
	}

	public function
	default($default) : cmsRequestQuery
	{
		$this->localProcessor->default($default);
		return $this;
	}

	public function
	out(string $keyOut) : cmsRequestQuery
	{
		$this->localProcessor->out($keyOut);
		return $this;
	}

	public  function
	data(string $key, array|object &$storage) : cmsRequestQuery
	{
		$this->localProcessor->data($key, $storage);
		return $this;
	}

	public function 
	validate($validateFlags)
	{
		$this->localProcessor->validate($validateFlags);
		return $this;
	}

	public function
	exec()
	{
		$this->localProcessor->exec();
		if($this->collectMode)
			$this->collectedProcessors[] = clone $this->localProcessor;
		return $this->localProcessor->getValue();
	}

	public function
	toArray() : array
	{
		$response = [];
		foreach($this->collectedProcessors as $processor)
		{
			$response[$processor->getKey()] = $processor->getValue();
		}
		return $response;
	}

	public function
	toObject(string $className = 'stdClass') : object
	{
		$response = new $className;
		foreach($this->collectedProcessors as $processor)
		{
			$response->{$processor->getKey()} = $processor->getValue();
		}
		return $response;
	}

	public function
	toJson() : string
	{
		return json_encode($this->toObject());
	}
}







/*

tk::dbug(
	cmsRequestQuery::prc()->get('t44est')->default('foobar1')->exec()
	);

$cmsRequestQuery = new cmsRequestQuery;

var_dump(
	$cmsRequestQuery->get('lol')->default(5)->validate(QueryValidation::TRIM | QueryValidation::CAST_INTEGER)->exec()
	);



$cmsRequestQuery2 = new cmsRequestQuery(true);
$cmsRequestQuery2->get('tedfst')->default('foobar3')->exec();
$cmsRequestQuery2->get('tedf32rrst')->default('foobar4')->exec();
	
tk::dbug(
	$cmsRequestQuery2->toObject()
	);

tk::dbug(
	$cmsRequestQuery2->toJson()
	);


tk::dbug(

	cmsRequestQueryValidation::set(
		QueryValidation::TRIM | QueryValidation::CAST_INTEGER
	)

);



	todo

		validation auf array prüfen und dann die childs recursiv mit prüfen

		option array nicht tiefer zu behandeln

		length funktion oder so ähnlch die qryValue bei string durch substr schickt		<--	und das da


		processor

			map funktion

			failOnPossibleInjection funktion		prüfung darauf noch ergänzen

			isPossibleInjection

		query

			map funktion

			failOnPossibleInjection funktion

			isPossibleInjection

*/