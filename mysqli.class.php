<?php

class Db {

    public $sayac;
    private $baglanti;
    private $hataGoster = true;
    public $karekter_seti = 'utf8';

	public function __construct()
	{
		global $config;
		
        $this->baglanti = mysqli_connect($config['db_host'], $config['db_username'], $config['db_password']) or die('MYSQL ile bağlantı kurulamadı');
            if($this->baglanti):
                mysqli_select_db($this->baglanti,$config['db_name']) or die('{"status": "0", "desc": "Unable to connect to db"}');
                $this->query('SET NAMES '.$this->karekter_seti);
            endif;
	}

	public function escapeArray($array)
	{
	    array_walk_recursive($array, create_function('&$v', '$v = mysql_real_escape_string($v);'));
		return $array;
	}
	
	public function to_bool($val)
	{
	    return !!$val;
	}
	
	public function to_date($val)
	{
	    return date('Y-m-d', $val);
	}
	
	public function to_time($val)
	{
	    return date('H:i:s', $val);
	}
	
	public function to_datetime($val)
	{
	    return date('Y-m-d H:i:s', $val);
	}

    /**Log Writer
     * ??*/
    private function writeLog($error){
        if(!empty($error)){
            $text = "Error Log: ".$error . " | ".date("d-M-Y h:i:s")."\n\n";
            file_put_contents("logs.txt",$text.PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
	
	public function query($sorgu)
        {
            $sorgu = mysqli_query($this->baglanti,$sorgu);
            if(!$sorgu && $this->hataGoster)
                $error ="";
                $error = mysqli_error($this->baglanti);
                $this->writeLog($error);
            return $sorgu;
        }
        
    public function insert($tablo, $veriler)
        {
            if(is_array($veriler)):
                $alanlar = array_keys($veriler);
                $alan = implode(',', $alanlar); 
                $veri = '\''.implode("', '",array_map(array($this, 'escapeString'), $veriler)).'\'';
            else:
                $parametreler = func_get_args();
                $tablo = array_shift($parametreler);
                $alan = $veri = null;
                $toplamParametre = count($parametreler)-1;
                foreach($parametreler as $NO => $parametre):
                    $bol = explode('=', $parametre, 2);
                    if($toplamParametre == $NO):
                        $alan .= $bol[0];
                        $veri .= '\''.$this->escapeString($bol[1]).'\'';
                    else:
                        $alan .= $bol[0].',';
                        $veri .= '\''.$this->escapeString($bol[1]).'\',';                    
                    endif;
                endforeach;
            endif;
            
            $ekle = $this->query('INSERT INTO '.$tablo.' ('.$alan.') VALUES ('.$veri.')');
            if($ekle)
                $res_body = "";
                $res_body = mysqli_insert_id($this->baglanti);
            if(strpos(strtoupper(mysqli_error($this->baglanti)),'DUPLICATE')!==false){
                return $this->response("2",$res_body);
            }
            return $this->response("1",$res_body);
        }
        
    public function table($sorgu)
        {
			$tablo = $this->query($sorgu);
			$sonuc = array();
			while($sonuclar = mysqli_fetch_object($tablo)):
				$sonuc[] = $sonuclar;
			endwhile;
			return $sonuc;
        }
        
    public function row($sorgu)
        {
            $satir = $this->query($sorgu);
            if($satir)
                return mysqli_fetch_object($satir);
        }
        
    public function field($sorgu)
        {
            $veri = $this->query($sorgu);
            if($veri):
                $sonuc = mysqli_fetch_array($veri,MYSQLI_NUM);
                return $sonuc[0];
            endif;            
        }
        
    public function delete($tablo, $kosul = null)
        {
            if($kosul):
                if(is_array($kosul)):
                    $kosullar = array();
                    foreach($kosul as $alan => $veri)
                        $kosullar[] = $alan.'=\''.$veri.'\'';
                endif;
                return $this->query('DELETE FROM '.$tablo.' WHERE '.(is_array($kosul)?implode(' AND ',$kosullar):$kosul));
            else:
                return $this->query('TRUNCATE TABLE '.$tablo);
            endif;
        }
        
    public function update($tablo, $deger, $kosul)
        {
            if(is_array($deger)):
                $degerler = array();
                foreach($deger as $alan => $veri)
                    $degerler[] = $alan."='".addslashes($veri)."'";
            endif;
            
            if(is_array($kosul)):
                $kosullar = array();
                foreach($kosul as $alan => $veri)
                    $kosullar[] = $alan."='".addslashes($veri)."'";
            endif;
            
            return $this->query('UPDATE '.$tablo.' SET '.(is_array($deger) ? implode(',',$degerler):$deger).' WHERE '.(is_array($kosul)?implode(' AND ',$kosullar):$kosul));
        }
        
    public function escapeString($veri)
        {
            if(!get_magic_quotes_gpc())
                return mysqli_escape_string($this->baglanti,$veri);
                
            return $veri;
        }


        /**response code reporting**/
    public  function response($status,$body){
        $arr = array("status"=>$status,"body"=>$body);
        return $arr;
    }
    
}
?>
