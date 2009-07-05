<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

# General configuration

# Database information
define(DB_DSN, "pgsql:dbname=filebin");
define(DB_USER, "filebin");
define(DB_PASS, "filebin");

# if you have memcache, enable this
define(USE_MEMCACHE, true);

# the directory to store files in
define(FILEBIN_STORE_PATH, "/home/slepp/filebin");

$_dbh = null;
function getDB() {
    global $_dbh;

    if($_dbh == null) {
        $_dbh = new PDO(DB_DSN, DB_USER, DB_PASS, array( PDO::ATTR_PERSISTENT => false ));

        $_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $_dbh->query("SET client_encoding TO 'UTF8'");
    }
    
    return $_dbh;
}

class FakeMemcache {
	function get($key) {
		return false;
	}
	function set($key, $value, $expires = 0) {
		return false;
	}
	function delete($key) {
		return false;
	}
}

function getMC() {
    global $_mch;
    
    if(USE_MEMCACHE) {
        if($_mch == null) {
            $_mch = new Memcache;
            $_mch->pConnect('127.0.0.1','11212');
        }
    } else {
        return new FakeMemcache;
    }

    return $_mch;
}

class Creator {
    var $id;
    var $remote;
    var $openid;

    function Creator($remote = null, $openid = null) {
        $this->id = null;
        if(!empty($openid)) {
            $this->byOpenID($openid);
        } else {
            $this->byRemote($remote);
        }
        if(!$this->id) {
            $this->remote = $remote;
            $this->openid = $openid;
            $this->store();
        }
    }
    
    function byOpenID($openid) {
        $sth = getDB()->prepare("SELECT * FROM creators WHERE openid=?");
        $sth->execute(array($openid));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        $sth = null;
        if($row) {
            $this->id = $row['creator_id'];
            $this->remote = $row['remote'];
            $this->openid = $row['openid'];
        } else {
            $this->id = null;
        }
    }
    
    function byRemote($remote) {
        $sth = getDB()->prepare("SELECT * FROM creators WHERE remote=?");
        $sth->execute(array($remote));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        $sth = null;
        if($row) {
            $this->id = $row['creator_id'];
            $this->remote = $row['remote'];
            $this->openid = $row['openid'];
        } else {
            $this->id = null;
        }
    }
    
    function store() {
        if($this->openid) {
            $sth = getDB()->prepare("INSERT INTO creators (openid) VALUES (?)");
            $sth->execute(array($this->openid));
            $sth = null;
            $this->byOpenID($this->openid);
        } else {
            $sth = getDB()->prepare("INSERT INTO creators (remote) VALUES (?)");
            $sth->execute(array($this->remote));
            $sth = null;
            $this->byRemote($this->remote);
        }
    }
}
    
class File {
    var $id;
    var $tag;
    var $path;
    var $params;
    var $valid;
    var $creator;
    
    function File($tag = null) {
        $this->valid = false;
        $this->creator = null;
        $this->byTag($tag);
    }
    
    function byTag($tag) {
        $this->valid = false;
        $this->creator = null;
        if($tag != null) {
            $sth = getDB()->prepare("SELECT * FROM filebin WHERE tag=? AND active");
            $sth->execute(array($tag));
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            $sth = null;
            $mcNeedsSet = true;
            if(!empty($row)) {
                $this->valid = true;
                $this->id = $row['file_id'];
                $this->tag = $row['tag'];
                $this->path = $row['path'];
                $this->params = $row;
                if($mcNeedsSet) {
                    getMC()->set("file".hash('md5',$tag),$row);
                    getMC()->set("filh".hash('md5',$tag), 0);
                }
            }
        }
    }
    
    function byID($id) {
        $this->valid = false;
        $this->creator = null;
        if($id != null) {
            $sth = getDB()->prepare("SELECT * FROM filebin WHERE file_id=? AND active");
            $sth->execute(array($id));
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            $sth = null;
            if(!empty($row)) {
                $this->valid = true;
                $this->id = $row['file_id'];
                $this->tag = $row['tag'];
                $this->path = $row['path'];
                $this->params = $row;
                getMC()->set("file".hash('md5',$tag),$row);
                getMC()->set("filh".hash('md5',$tag), 0);
            }
        }
    }
    
    function byPath($url) {
        $this->valid = false;
        $this->creator = null;
        if(($tag = getMC()->get("file".hash('md5',$tag))) === false) {
            $sth = getDB()->prepare("SELECT tag FROM filebin WHERE path=? AND active");
            $sth->execute(array($url));
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            $sth = null;
            if(!empty($row)) {
                $this->byTag($row['tag']);
            }
        } else {
            $this->byTag($tag);
        }
    }
    
    function byUploadID($url) {
        $this->valid = false;
        $this->creator = null;
        if(($tag = getMC()->get("file".hash('md5',$tag))) === false) {
            $sth = getDB()->prepare("SELECT tag FROM filebin WHERE file_id=(SELECT file_id FROM upload_tracking WHERE upload_id=? ORDER BY created DESC LIMIT 1) AND active");
            $sth->execute(array($url));
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            $sth = null;
            if(!empty($row)) {
                $this->byTag($row['tag']);
            }
        } else {
            $this->byTag($tag);
        }
    }
    
    function store() {
        if($this->valid) { // This is valid, so just UPDATE
            $sth = getDB()->prepare("UPDATE filebin SET path=?,active=?,name=?,size=?,content_type=? WHERE tag=?");
            $sth->execute(array($this->path,
                                (isset($this->params{'active'})?$this->params{'active'}:'t'),
                                $this->params{'name'},
                                $this->params{'size'},
                                $this->params{'content_type'},
                                $this->tag)
                          );
            $sth = null;
            getMC()->delete("file".hash('md5',$this->tag));
            $this->byTag($this->tag);
        } else {
            $sth = getDB()->prepare("INSERT INTO filebin (tag,path,active,name,size,content_type,creator) VALUES (?,?,?,?,?,?,?)");
            $sth->execute(array($this->tag,
                                $this->path,
                                (isset($this->params{'active'})?$this->params{'active'}:'t'),
                                $this->params{'name'},
                                $this->params{'size'},
                                $this->params{'content_type'},
                                ($this->creator?$this->creator->id:null))
                          );
            $sth = null;
            $this->byTag($this->tag);
            $sth = getDB()->prepare("INSERT INTO statistics (file_id,hits) VALUES (?,0)");
            $sth->execute(array($this->params{'file_id'}));
            $sth = null;
        }
    }
    
    function isValid() {
        return $this->valid;
    }
    
    function incrementHits() {
        if($this->valid) {
            $sth = getDB()->prepare("UPDATE statistics SET hits=hits+1,last_hit=now() WHERE file_id=?");
            $sth->execute(array($this->params['file_id']));
            $sth = null;
        }
    }
    
    function incrementShrinks() {
        if($this->valid) {
            $sth = getDB()->prepare("UPDATE filebin SET creations=coalesce(creations+1,1) WHERE tag=?");
            $sth->execute(array($this->tag));
            $sth = null;
        }
    }
}

class FileUtil {
    function removeUploadID($id) {
        $sth = getDB()->prepare("DELETE FROM upload_tracking WHERE upload_id=?");
        $sth->execute(array($id));
    }
}

function kbFormat($v) {
        if($v > 900000) {
                    return number_format( ($v / 1024 / 1024), 2) . "MB";
        }
        if($v > 900) {
                    return number_format( ($v / 1024), 1) . "KB";
        }
        return $v . " bytes";
}
?>
