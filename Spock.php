<?php

namespace Spock;

class Spock {

	private $cacheFile, $maxAge, $path;

	private $paths = [
		'static' => 'staticCache', // Files in this folder are usually never changed (time goes to infinite).
		'temp'	 => 'tmpCache' 	   // Already in this other folder, the files may be updated periodically.
	];


	/**
	 * Spock will save objects in the file system after serialize it,
	 * and apply it to gzdeflate() function.
	 *
	 * @param string  $objectID Something that identifies the object that is being cached amazenado.
	 * 							It can be an URL, a primary key, etc. Warning: Must be unique in the
	 * 							set of objects, to represent the same object.
	 *
	 * @param string  $maxAge	If left empty, the cached object will not expire. If the object must
	 * 							have an expiration time, it should be set as a string in the syntax
	 * 							of GNU (http://www.gnu.org/software/shishi/manual/html_node/Date-input-formats.html).
	 */
	public function __construct($objectID, $maxAge = INF)
	{
		// Get the path to save the working file.
		$this->path = ($maxAge === INF) ? $this->paths['static'] : $this->paths['temp'];

		// Set the name of the working file.
		$this->cacheFile = $this->path . DIRECTORY_SEPARATOR . sha1($objectID) . '.spock';
		$this->maxAge = $maxAge;
	}

	/**
	 * Tries to retrieve an object in the file system. If you find it, and still is valid, returns
	 * the object. Otherwise, returns false.
	 *
	 * @return mixed
	 */
	public function fetch()
	{
		if($this->isFresh())
			return $this->getContent();
		return false;
	}

	/**
	 * Saves a cached object in the file system.
	 *
	 * @param mixed  $content The object to be stored. Stores all types, except the resource-type.
	 */
	public function push($content)
	{
		if(!file_exists($this->path))
			mkdir($this->path, 0755);
		file_put_contents($this->cacheFile, gzdeflate(serialize($content)));
	}

	/**
	 * Starts to save the content of a rendered MVC's View, for example, in the buffer.
	 */
	public function startBuffering()
	{
		ob_start();
	}

	/**
	 * Stop to save the rendered content, and saves it to cache.
	 * @param  boolean $output  If false, the function returns the output buffer rather than print it.
	 * @return mixed
	 */
	public function stopBuffering($output = true)
	{
		$output = ob_get_contents();
		ob_end_clean();
		$this->push($output);

		if(!$output)
			return $output;

		echo $output;
	}

	/**
	 * Check if an given file is fresh (exists, and is updated).
	 * @return boolean
	 */
	private function isFresh()
	{

		if(!file_exists($this->cacheFile))
			return false;

		if($this->maxAge === INF)
			return true;

		$now = time();
		$expireTime = strtotime($this->maxAge, filemtime($this->cacheFile));
		return ($now <= $expireTime);
	}

	/**
	 * Retrieves an object saved in a file.
	 * @return mixed  The object.
	 */
	private function getContent()
	{
		$content = file_get_contents($this->cacheFile);
		return unserialize(gzinflate($content));
	}
}
