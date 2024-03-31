#!/bin/bash

# Seedbox provisioning script by Onlyodin
#
# Placeholders below are used in the default config files and replaced when deployed

#@@scgi@@
#@@user@@
#@@port@@
#@@ip@@
#@@rpc@@

# This should be the default IP of the host
ip="12.34.56.78"

# Define everything here in an array
user=(user1 user2 user3)
scgi=(20876 20879 20882)
port=(22877 22880 22883)
rpc=(RPC001 RPC002 RPC003)

# Create the rtgroup group before we begin
groupadd -r rtgroup

# Create the SFTP root (needed for chroot) before we begin
mkdir /sftp
chown root:rtgroup /sftp
chmod 750 /sftp

# Update the loop to the number of users in the array
# eg. for three users, start at zero, {0..2}
# This increments by one each iteration.
for i in {0..2}; do

# Create a logical volume and format
lvcreate -L 100G -n ${user[$i]} /dev/data
mkfs.ext4 -m 0 /dev/data/${user[$i]}

# Create the home directory and mount the LV
mkdir /home/${user[$i]}
chmod 000 /home/${user[$i]}

mount /dev/data/${user[$i]} /home/${user[$i]}

# Add mounts to fstab, for future reboots
echo "/dev/data/${user[$i]} /home/${user[$i]} ext4 defaults 0 0" >>/etc/fstab
echo "home/${user[$i]}/watch/ /sftp/${user[$i]}/watch none defaults,bind 0 0" >>/etc/fstab
echo "/home/${user[$i]}/downloads/ /sftp/${user[$i]}/downloads none defaults,bind 0 0" >>/etc/fstab

# Create the user account
# -M does not create the home directory because we already did that
# -g rtgroup sets the primary group to rtgroup
# Change this to -G rtgroup if you want the user to have a personal default group
# Be mindful that if using -G you will need to set sticky gid on directories
useradd -M -g rtgroup ${user[$i]}

# Generate the .rtorrent.rc file and place in home
cat dot.rtorrent.rc |sed -e "s/@@user@@/${user[$i]}/g" -e "s/@@scgi@@/${scgi[$i]}/g" -e "s/@@port@@/${port[$i]
}/g" -e "s/@@ip@@/${ip}/g" >/home/${user[$i]}/.rtorrent.rc

# Create the directories we need going forward
mkdir /home/${user[$i]}/.session
mkdir /home/${user[$i]}/watch
mkdir /home/${user[$i]}/downloads
mkdir -p /sftp/${user[$i]}/watch
mkdir -p /sftp/${user[$i]}/downloads
chmod 000 /sftp/${user[$i]}/watch
chmod 000 /sftp/${user[$i]}/downloads

mkdir -p /var/www/ruTorrent/conf/users/${user[$i]}
#mkdir -p /var/www/ruTorrent/share/users/${user[$i]}
mkdir -p /var/www/ruTorrent/share/users/${user[$i]}/torrents

# Give the user ownership of their own home
chown -R ${user[$i]}:rtgroup /home/${user[$i]}
# Set-gid on watch and downloads for the webserver
chmod g+ws /home/${user[$i]}/watch /home/${user[$i]}/downloads

# And their ruTorrent directories too
chown -R ${user[$i]}:rtgroup /var/www/ruTorrent/share/users/${user[$i]}
chmod -R g+ws /var/www/ruTorrent/share/users/${user[$i]}

# autodl_ is for SysV systems, rtor_.service is for systemd implementations
#cat autodl_ |sed -e "s/@@user@@/${user[$i]}/g" >/etc/init.d/autodl_${user[$i]}
cat rtor_.service |sed -e "s/@@user@@/${user[$i]}/g" >/usr/lib/systemd/system/rtor_${user[$i]}.service

# Deploy the user's ruTorrent config
cat config.php |sed -e "s/@@user@@/${user[$i]}/g" -e "s/@@scgi@@/${scgi[$i]}/" -e "s/@@rpc@@/${rpc[$i]}/" >/var/www/ruTorrent/conf/users/${user[$i]}/config.php

# Deploy SCGI configuration for the user within nginx configuration
cat scgi-default.conf |sed -e "s/@@user@@/${user[$i]}/g" -e "s/@@scgi@@/${scgi[$i]}/" >/etc/nginx/conf.d/scgi-${user[$i]}.conf

# Add firewalld configuration
firewall-cmd --permanent --new-service=rtor-${user[$i]}
firewall-cmd --permanent --service=rtor-${user[$i]} --add-port=${port[$i]}/tcp
firewall-cmd --permanent --service=rtor-${user[$i]} --add-port=${port[$i]}/udp
firewall-cmd --permanent --add-rich-rule="rule family=ipv4 destination address=${ip} service name=rtor-${user[$i]} accept"

# Move on to the next user, or finish up
done

# Reload new fstab and services in systemd
systemctl daemon-reload
# Mount everything
mount -a

echo "Finished. Don't forget to set passwords for the users."
# Here ends the script.
