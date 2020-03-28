<?PHP

    class Host
    {
        public $name;
        public $ip;
    }
    
    $removeip = isset($_GET['removeip']) ? $_GET['removeip'] : '';
    $removename = isset($_GET['removename']) ? $_GET['removename'] : '';
    $addip = isset($_GET['addip']) ? $_GET['addip'] : '';
    $addname = isset($_GET['addname']) ? $_GET['addname'] : '';
    
    // address=/test.dev/127.0.0.1
    // /usr/local/etc/dnsmasq.conf
    $fHost = fopen("/usr/local/etc/dnsmasq.conf", "r") or die("Unable to open hosts!");
    $hostsStr = fread($fHost, filesize("/usr/local/etc/dnsmasq.conf"));
    fclose($fHost);
    
    $lines = explode ("\n", $hostsStr);
    
    $hostData = array();
    
    foreach ($lines as $l) {
        
        $sl = trim($l);
        
        if (substr($sl, 0, 1) === "#") continue;
        
        $lineContent = explode("=", $sl);
        
        if (count($lineContent) != 2) continue;
        if (strcmp($lineContent[0], "address") != 0) continue;
        
        $addrContent = explode("/", $lineContent[1]);
        
        $newContent = array();
        foreach ($addrContent as $asubstring) {
            if (strlen($asubstring) != 0 && strcmp($asubstring, "\t") != 0 && strcmp($asubstring, "\n") != 0 && strcmp($asubstring, "/") != 0) {
                array_push($newContent, $asubstring);
            }
        }
        
        if (count($newContent) != 2) continue;
        
        $hostData[$newContent[0]] = $newContent[1];
    }
    
    if (strlen($removeip) > 0 && strlen($removename) > 0) {
        // remove
        // echo "remove " . $removeip . "  " . $removename;
        if (array_key_exists($removename, $hostData)) {
            if (strcmp($hostData[$removename], $removeip) == 0) {
                unset($hostData[$removename]);
            }
        }
    }
    else if (strlen($addip) > 0 && strlen($addname) > 0) {
        // add
        echo "add " . $addip . "  " . $addname;
        $hostData[$addname] = $addip;
    }
    
    $fHost = fopen("/usr/local/etc/dnsmasq.conf", "w") or die("Unable to open hosts!");
    foreach ($hostData as $key=>$val) {
        fwrite($fHost, "address=/" . $key . "/" . $val . "\n");
        fseek($fHost, 0, SEEK_END);
    }
    fclose($fHost);
    
    if (strlen($removeip) > 0 || strlen($removename) > 0 || strlen($addip) > 0 || strlen($addname) > 0) {
        file_put_contents("./flag_refresh_dnsmasq" , "true");
        header("Location: index.php");
    }
    
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<title>修改DNS配置</title>';
    echo '<link rel="stylesheet" href="styles.css">';
    echo '</head>';
    echo '<body>';
    
    echo "<div class=\"card\">\n";
    echo "<p><label class=\"text\">修改DNS配置</label></p>";
    echo "</div>";
    
    foreach ($hostData as $key=>$val) {
        echo "<div class=\"card\">\n";
        echo "<p><label class=\"text\"><button onclick=\"deleteHost('{$key}', '{$val}')\">删除Host</button> ip: [{$val}] host: [{$key}] </label></p>";
        echo "</div>";
    }
    
    echo '<div class="card">';
    echo '<p class="text">ip: </p><p class="text"><input class="input" type="text" id="ip" value=""></p>';
    echo '<p class="text">name: </p><p class="text"><input class="input" type="text" id="name" value=""></p>';
    echo "<p><label class=\"text\"><button onclick=\"addHost()\">添加DNS解析</button></label></p>";
    echo '</div>';
    
?>
    
<script>

function addHost() {
    
    var ip = document.getElementById("ip").value;    
    var name = document.getElementById("name").value;
    
    if (ip.length == 0 || name.length == 0) {
        alert("请先配置ip和host再试！");
        return;
    }

	if (window.confirm('你确定要添加 [' + ip + '] : [' + name + '] 吗？')) {
        window.location.replace("index.php?&addip=" + ip + "&addname=" + name);
        return true;
	}
}

function deleteHost(name, ip) {

	if (window.confirm('你确定要移除 [' + ip + '] : [' + name + '] 吗？')) {
        window.location.replace("index.php?&removeip=" + ip + "&removename=" + name);
        return true;
	}
}

</script>

</body>
</html>
