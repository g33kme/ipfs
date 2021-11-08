# IPFS PHP
Easy to use PHP Wrapper for IPFS to work with the HTTP API of IPFS. When an IPFS node is running as a daemon, it exposes an HTTP API that allows you to control the node.

In many cases, using this API this is preferable to embedding IPFS directly in your program — it allows you to maintain peer connections that are longer lived than your app and you can keep a single IPFS node running instead of several if your app can be launched multiple times

🙏 You own some ADA? Help us improve this project by staking your ADA to our pool with ticker: **GEEK**  
[GeekMe Stake Pool](https://adapools.org/pool/c13debc5c24d045cf5e2d69c33ff981602ae55d8bded995a6d930836)

## 1. Install and use IPFS PHP

If you don't have any IPFS Node API running where you can connect IPFS PHP to, please follow first the installation steps in part 2.

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require g33kme/ipfs
```

You can also simply manually include `source/ipfs.php` from this repository to your project and use the IPFS PHP Class.

```php
include('path/to/my/ipfs.php');
```

## Requirements

This library requires PHP 7.1 or later and if you use Composer you need Version 2+.

```php

/*
 * You have to setup some Settings
 */

// Define your URL to your IPFS NODE API, with version, but NO ending slash!
define('IPFS_API', 'http://<IP4-SERVER-ADDRESS>:5001/api/v0');

// Check version of your IFPS Node
$res = IPFS::version();
print_r($res);

//Add a file to your IFPS Node and get a hash back
$res = IPFS::add(array(
    'file' => 'path/to/your/geekme.png'
));
print_r($res);
echo $res[Name].' was uploaded to IPFS with hash: '.$res[Hash];
```



## 2. Installation: IPFS Node

If you have already a running IPFS node where your can connect our IPFS PHP Wrapper to, you can skip the installation part.

⭕ **Important!** 

IPFS can run on most Linux, macOS, and Windows systems. We recommend running it on a machine with:

- Min 2 vCPU 
- Min 2GB of RAM (4GB Recommended)
- 50 GB of disk space (Ideally SSD)
- Good internet connection (at least 10Mbps)

Alright, let's try to setup your IPFS node, we running on **Ubuntu 20.04 LTS**

### 2.1 GoLang is needed for running your IPFS node

```bash
# Update operation system
apt-get update 
apt-get upgrade

# GoLang is needed for running IPFS
# Install latest GoLang, (At the time of this guide, the current version of GoLang is 1.17.2. You may want to replace the version of GoLang if a newer version becomes available)
wget https://dl.google.com/go/go1.17.2.linux-amd64.tar.gz

# Extract GoLang to /usr/local
tar -C /usr/local -xzf go1.17.2.linux-amd64.tar.gz

# Open up your .profile
# With any editor your want or nano
nano ~/.profile

# Add these lines to the bottom of the file
export PATH=$PATH:/usr/local/go/bin
export GOPATH="$HOME/go"
PATH="$GOPATH/bin:$PATH"

# Optional, If you’d like to define a specific path for IPFS instead of the default ~/.ipfs path, please include the following line at the end of the file:
export IPFS_PATH=/your/custom/path

# When you are on nano exit with CRTL+x, save with y, and hit enter to confirm

# Then reload your profile 
source ~/.profile

# Check that your installation was successful
# You should be greeted with something like: “go version go1.12.1 linux/amd64”. If you received this message, congrats! GoLang was successfully installed
go version
```

### 2.2 Installing IPFS with ipfs-update

First, you’ll want to install [ipfs-update](https://github.com/ipfs/ipfs-update). This handy package allows you to easily update your ipfs client whenever there are new updates without having to go through a bunch of manual upgrade headaches.

```bash
# Download and get
go get -u github.com/ipfs/ipfs-update

# Once this is complete, go ahead and run this command:
ipfs-update versions

# Take note of whatever version is the latest and run something like this
# You should receive a message that says “Installation complete!”
ipfs-update install v0.10.0

# Now run
# You should see a message that you initializing IPFS node, generating keypair and get your peer identity
ipfs init --profile server

# Depending on your server storage you want to increase your IPFS size from default 10GB to let's say 50GB
ipfs config Datastore.StorageMax 20GB

# Now you can start your IPFS daemon
# Your IPFS node is now officially running
ipfs daemon
```

### 2.3 Creating an IPFS service so your IPFS node is running all the time

If you exit your SSH session on your Server, IPFS will shut down.
You can fix this by creating an ubuntu systemd service to keep things running.

```bash
# Create a systemd service file
# Attention, If you chose a custom path for IPFS, you’ll want to use that in the environment instead of the default IPFS path.
nano /etc/systemd/system/ipfs.service

# Enter the following code in your editor
[Unit]
Description=IPFS Daemon
[Service]
Type=simple
ExecStart=/root/go/bin/ipfs daemon --enable-gc
Group=root
Restart=always
Environment="IPFS_PATH=/root/.ipfs"
[Install]
WantedBy=multi-user.target

# Save your new file on nano with CRTL+x, y, and then confirming by hitting ENTER

# Check this commands in your termian to enable your new IPFS service
systemctl daemon-reload
systemctl enable ipfs
systemctl start ipfs

# Check and see the status of your created IPFS service
# You should see some satus "active (running) in your console
systemctl status ipfs

# You may try to restart your server and check your IPFS service
shutdown -r now

# Once the reboot is complete, SSH back into your server and run the following to see if you have an IPFS node up and running:
# You should see a few peers your ipfs node is connected to
ipfs swarm peers
```

### 2.4 Incoming Ports of your IPFS Node

⭕ **Very important!** 

Your IPFS Node creates a public accessible HTTP API and open some other ports to work with other IPFS nodes.
At easiest and best simple create firewall rules to access the cardano-wallet API only from specific IPs. 
You can create firewalls, mostly free, on popular hosting providers like Hetzner, Vultr or DigitalOcean. Of course you can setup a custom firewal rule 
in your operating system as well. 

**TCP 4001**  
This is the primary port that your IPFS node will use to communicate to other IPFS nodes, you should keep that open for any IP4/6 address.

**TCP 5001**  
This is your IPFS API to connect our IPFS PHP library to, you should only allow inbound traffic on specific IPs

**TCP 8080**  
This is needed if you want to run your node as a gateway node. This is the port where you can see the content of your IPFS has. 
For example, you can retrieve an image of some of our Geekme Logos [Geekme Logo](https://ipfs.geekme.com/ipfs/QmXt1kKcQgE347qW7gPeMpmbDJqGAgF34AFcXjkuCyUx4h)

**TCP 8081**  
This is needed if you want to run pubsub capabilities on your node.

### 1.5 Changing listen IP address and ports

You may run your IPFS node on other ports and run on your public IP address to access. 
Simply set your listen IP address to `0.0.0.0` to listen on all available IPs of your server. 
E.g set as API config: `/ip4/0.0.0.0/tcp/5001`

```bash
# Simply set in your api/config file of your IPFS node
/root/.ipfs/api
/root/.ipfs/config

# restart the IPFS Node service
systemctl restart ipfs
```

You may also add some peers from popular sites as cloudflare, pinata etc, so your uploaded files on your IPFS node get sync faster. 
Open `/root/.ipfs/config` file and change "Bootstrap" Contet to the following
```json
"Bootstrap": [
    "/dnsaddr/bootstrap.libp2p.io/p2p/QmNnooDu7bfjPFoTZYxMNLWUQJyrVwtbZg5gBMjTezGAJN",
    "/dnsaddr/bootstrap.libp2p.io/p2p/QmQCU2EcMqAqQPR2i9bChDtGNJchTbq5TbXJJ16u19uLTa",
    "/dnsaddr/bootstrap.libp2p.io/p2p/QmbLHAnMoJPWSCR5Zhtx6BHJX9KiKNN6tpvbUcqanj75Nb",
    "/dnsaddr/bootstrap.libp2p.io/p2p/QmcZf59bWwK5XFi76CZX8cbJ4BhTzzA3gU1ZjYZcYW3dwt",
    "/ip4/104.131.131.82/tcp/4001/p2p/QmaCpDMGvV2BGHeYERUEnRQAwe3N8SzbUtfsmvsqQLuvuJ",
    "/ip4/104.131.131.82/udp/4001/quic/p2p/QmaCpDMGvV2BGHeYERUEnRQAwe3N8SzbUtfsmvsqQLuvuJ",
    "/dnsaddr/nyc1-1.hostnodes.pinata.cloud/ipfs/QmRjLSisUCHVpFa5ELVvX3qVPfdxajxWJEHs9kN3EcxAW6",
    "/dnsaddr/fra1-1.hostnodes.pinata.cloud/ipfs/QmWaik1eJcGHq1ybTWe7sezRfqKNcDRNkeBaLnGwQJz1Cj",
    "/ip6/2606:4700:60::6/tcp/4009/ipfs/QmcfgsJsMtx6qJb74akCw1M24X1zFwgGo11h1cuhwQjtJP",
    "/ip4/172.65.0.13/tcp/4009/ipfs/QmcfgsJsMtx6qJb74akCw1M24X1zFwgGo11h1cuhwQjtJP"
  ], 
```

```bash
# restart the IPFS Node service
systemctl restart ipfs

# and check your bootstrap
ipfs bootstrap
```


## 🙏 Supporters

You own some ADA? Stake your ADA to our pool with ticker: GEEK  
[GeekMe Stake Pool](https://adapools.org/pool/c13debc5c24d045cf5e2d69c33ff981602ae55d8bded995a6d930836)  

☕ Wanna buy me a coffee or two? Send some ADA to our donation address: 
addr1qxksn95zhgje7tvdsgfpk9t49sssz4fqewt74neh56cnl4ml8zpc3556jh8exfp70a6f3pva7yf4fmfmw52tdh3dh94sqdvu27