#!/bin/bash

set -e

echo "=============================="
echo " Building module package..."
echo -e "==============================\n"

echo "[INFO] Removing build directory..."
rm -rf build/
echo "[OK] Removing build directory completed successfully."

echo "[INFO] Creating module directories..."
mkdir -p build/modules/gateways/callback/
mkdir -p build/modules/gateways/tpay/
echo "[OK] Creating module directories completed successfully."

echo "[INFO] Copying module files..."
cp src/tpay.php build/modules/gateways/tpay.php
cp src/tpay_callback.php build/modules/gateways/callback/tpay.php

cp -r src/. build/modules/gateways/tpay/

rm -f build/modules/gateways/tpay/tpay.php
rm -f build/modules/gateways/tpay/tpay_callback.php

cp composer.json build/modules/gateways/tpay/composer.json
cp composer.lock build/modules/gateways/tpay/composer.lock
echo "[OK] Copying module files completed successfully."

echo "[INFO] Updating composer.json..."
php -r '
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
  $file = __DIR__ . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "gateways" . DIRECTORY_SEPARATOR . "tpay" . DIRECTORY_SEPARATOR . "composer.json";
  $json = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);

  if (!isset($json->autoload->{"psr-4"}->{"JakubFilip\\Tpay\\"})) {
    throw new RuntimeException("Invalid composer.json");
  }

  $json->autoload->{"psr-4"}->{"JakubFilip\\Tpay\\"} = "./";

  $newContent = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
  file_put_contents($file, $newContent . PHP_EOL);
} catch (Throwable $e) {
  echo "Failed to update composer.json: " . $e->getMessage() . PHP_EOL;
  exit(1);
}
'
echo "[OK] Updating composer.json completed successfully."

echo "[INFO] Installing Composer dependencies..."
if ! composer install --working-dir="build/modules/gateways/tpay/" --no-dev --classmap-authoritative --no-interaction --no-progress > /dev/null 2>&1; then
  echo "Failed to install Composer dependencies"
  exit 1
fi
echo "[OK] Installing composer dependencies completed successfully."

echo "[INFO] Removing Composer configuration files..."
rm -f build/modules/gateways/tpay/composer.json
rm -f build/modules/gateways/tpay/composer.lock
echo "[OK] Removing Composer configuration files completed successfully."

echo -e "\n=============================="
echo " Building module package completed successfully"
echo "=============================="
