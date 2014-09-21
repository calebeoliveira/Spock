Spock
=====

Spock will save objects in the file system, identifying it by one unique ID.
After, the object can be restored quickly. 

Example:

```
$spock = new Spock\Spock('imgID', '1 hour');

// Tries to recover...
$img = $spock->fetch();

// If the recover fails...
if($img === false) {
	$img = new LazyGDLibrary('param');
	$spock->push($img);
}

$img->makeSomeMagic();
```

The above example shows a simple case where Spock attempts to recover an object identified as 'imgID' from the file system. If they find it, will put in `$img` variable. Otherwise, will process as usual, and save the result. 

In addition, the cache is configured for 1 hour, which means that the slow code will only run once every hour, and in other times that prompted this range, it sends the cached file instead of reprocessing all.
