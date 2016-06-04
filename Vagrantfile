# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "debian/jessie64"
  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.synced_folder ".", "/vagrant", id: "v-root", mount_options: ["rw", "tcp", "nolock", "noacl", "async"], type: "nfs", nfs_udp: false
  config.vm.provision "shell", inline: <<-SHELL
    # Force to move to /vagrant on login
    if ! grep -q "cd /vagrant" /home/vagrant/.bashrc
    then
      echo "cd /vagrant" >> /home/vagrant/.bashrc
    fi
    echo 'deb http://packages.dotdeb.org jessie all' | sudo tee /etc/apt/sources.list.d/dotdeb.list >/dev/null 2>&1
    wget -O- -q https://www.dotdeb.org/dotdeb.gpg | sudo apt-key add -

    echo "Update debian package list"
    sudo apt-get update >/dev/null 2>&1
    echo "Installing some packages"
    sudo apt-get -y install curl git php7.0-cli php7.0-curl php7.0-intl vim >/dev/null 2>&1
    cat >/etc/php/mods-available/custom.ini <<EOF
date.timezone = 'UTC'
error_reporting = E_ALL
display_errors = On
display_startup_errors = On
phar.readonly = Off
EOF
    sudo ln -s /etc/php/mods-available/custom.ini /etc/php/7.0/cli/conf.d/99-custom.ini

    echo "Installing latest version of php-cs-fixer"
    if [ -f "/usr/local/bin/php-cs-fixer" ]
    then
      sudo /usr/local/bin/php-cs-fixer selfupdate
    else
      curl -LSs http://get.sensiolabs.org/php-cs-fixer.phar -o php-cs-fixer
      chmod +x php-cs-fixer
      sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer
    fi

    echo "Installing latest version of composer"
    if [ -f "/usr/local/bin/composer" ]
    then
      sudo /usr/local/bin/composer selfupdate
    else
      php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
      sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1
      php -r "unlink('composer-setup.php');"
    fi

    echo "Installing latest version of phpunit"
    if [ -f "/usr/local/bin/phpunit" ]
    then
      sudo /usr/local/bin/phpunit --self-update
    else
      wget -q https://phar.phpunit.de/phpunit.phar
      chmod +x phpunit.phar
      sudo mv phpunit.phar /usr/local/bin/phpunit
    fi

    echo "Installing latest version of box"
    curl -LSs https://box-project.github.io/box2/installer.php | php >/dev/null 2>&1
    chmod +x box.phar
    sudo mv box.phar /usr/local/bin/box

    git --version
    php --version | grep "^PHP"
    box --version
    composer --version 2>/dev/null
    php-cs-fixer --version
    phpunit --version

  SHELL
end
