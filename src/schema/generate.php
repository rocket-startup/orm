<?php

	define('PATH_ROOT', explode('vendor/astronphp', __DIR__)[0]);
	require_once PATH_ROOT."vendor/autoload.php";
	$dir = PATH_ROOT."library/entity/";

	// o Doctrine utiliza namespaces em sua estrutura, por isto estes uses
	use Doctrine\ORM\Tools\Setup;
	use Doctrine\ORM\EntityManager;

	//onde irão ficar as entidades do projeto? Defina o caminho aqui
	$isDevMode = true;

	$dbParams = array(
	    'driver'   => 'pdo_mysql',
	    'user'     => 'vagrant',
	    'password' => 'vagrant',
	    'dbname'   => 'orm',
	);

	if (is_dir($dir)) {
	    $iterator = new \FilesystemIterator($dir);
	    if ($iterator->valid()) {
	        $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
	        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
	        foreach ( $ri as $file ) {
	            $file->isDir() ?  rmdir($file) : unlink($file);
	        }
	    }
	}
	//setando as configurações definidas anteriormente
	$config = Setup::createAnnotationMetadataConfiguration(array($dir), $isDevMode);
	//criando o Entity Manager com base nas configurações de dev e banco de dados
	$em = EntityManager::create($dbParams, $config);
	$em->getConfiguration()->setMetadataDriverImpl(
	    new \Doctrine\ORM\Mapping\Driver\DatabaseDriver(
	        $em->getConnection()->getSchemaManager()
	    )
	);
	$cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
	$cmf->setEntityManager($em);
	$metadata = $cmf->getAllMetadata();

	$generator = new \Doctrine\ORM\Tools\EntityGenerator();
	$generator->setUpdateEntityIfExists(true);
	$generator->setGenerateStubMethods(true);
	$generator->setGenerateAnnotations(true);
	$generator->setNumSpaces(5);
	$generator->generate($metadata, $dir);

		$assignature="<?php\nnamespace Library\Entity;\n";

		$addClass="}\n\n 	public function getNameTable()\n	{ \n 		return '@NameTable';\n	} \n}";

		$procurar = array("@ORM\\","private",")\n    {","\n{","<?php\n\n\n\n","}\n}");
		$colocar = array("@","protected","){","{",$assignature,$addClass);

	    $types = array('php');
		$path = new DirectoryIterator($dir);

		$contador=0;
		foreach ($path as $fileInfo) {
		    $ext = strtolower( $fileInfo->getExtension() );
		    if( in_array( $ext, $types ) ){

	    	$arquivo = $dir.$fileInfo->getFilename();

	    	if(($fp = fopen($arquivo, "r"))) {
		    	$ponteiro = fopen ($arquivo,"r");

				//LÊ O ARQUIVO ATÉ CHEGAR AO FIM
				while (!feof ($ponteiro)) {
				  $linha = fgets($ponteiro,4096);
				  $tmpLine=$linha;

				  if(strpos($tmpLine,'(\\')!==false){
				  	$tmp = explode("(",$tmpLine);
				  	$tmp = explode("$",$tmp[1]);

				  	if($n=array_search($tmp[0],$procurar)){
				  		$procurar[$n] = $tmp[0];
				  		$colocar[$n] = "";
				  	}else{
					  	$procurar[] = $tmp[0];
					  	$colocar[] = "";
					}

				  }
				  if(strpos($tmpLine,'* @ORM\Table(name="')!==false){
				  	$nametable = explode('"', $tmpLine);
				  	if($p=array_search('@NameTable',$procurar)){
				  		$procurar[$p] = '@NameTable';
				  		$colocar[$p] = $nametable[1];
				  	}else{
					  	$procurar[] = '@NameTable';
					  	$colocar[] = $nametable[1];
					}
				  }

				}
			}
			//Obtem o conteudo do arquivo
			$obter = file_get_contents($arquivo);
			$novo = str_replace($procurar, $colocar, $obter);

			//Grava o novo texto (modificado) no arquivo
			$gravar = fopen($arquivo, "w");
			fwrite($gravar, $novo);
			fclose($gravar);
			$contador++;

	    }
	}
	if($contador>0){
		echo "\e[0;30;42mGenerated ".($contador)." new classes in '".$dir."'\e[0m\n";
	}else{
		echo "No generated classes\n";
	}