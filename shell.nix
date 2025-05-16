{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php
    pkgs.phpPackages.composer
    pkgs.nodejs-18_x
    pkgs.libmysqlclient
    pkgs.nginx
  ];
}
