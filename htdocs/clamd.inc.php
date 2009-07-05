<?php
/*
  This file is part of the Filebin package.
  Copyright (c) 2003-2009, Stephen Olesen
  All rights reserved.
  More information is available at http://filebin.ca/
*/

define(CLAMD_SOCK, "/var/run/clamav/clamd.sock");

## Scans a file. Returns true if it's clean.
function scanFile($fname) {
    $sock = socket_create(AF_UNIX, SOCK_STREAM, 0);
    
    if($sock === false)
      return true;
    
    if(socket_connect($sock, CLAMD_SOCK, 0)) {
        socket_write($sock, "SESSION\n");
        socket_write($sock, "PING\n");
        $out = socket_read($sock, 2048);
        if($out != "PONG\n") {
            return true;
        }
        socket_write($sock, "SCAN $fname\n");
        
        socket_write($sock, "END\n");
        
        while($out = socket_read($sock, 2048)) {
            $buf .= $out;
        }
        
        $lines = split("\n", $buf);
        print_r($lines);
        
        $retval = true;
        
        foreach($lines as $l) {
            if(preg_match('/^(.*): (.*) FOUND$/', $l, $m)) {
                $retval = false;
            }
        }
    
        socket_close($sock);
    } else {
        # If we can't reach Clamd, then we declare it "ok" in the desire
        # to keep functioning.
        $retval = true;
    }
    
    return $retval;
}
?>
