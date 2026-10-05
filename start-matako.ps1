# Starts everything MataKo needs after the laptop restarts:
# the Android emulator, the Laravel backend, the Expo dev server, and the MataKo app.
# Run from PowerShell:  powershell -ExecutionPolicy Bypass -File D:\MataKO\MataKO\start-matako.ps1

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$sdk = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
$adb = Join-Path $sdk 'platform-tools\adb.exe'
$emulator = Join-Path $sdk 'emulator\emulator.exe'
$avd = 'Pixel_9_Pro'

function Test-Port([int] $port) {
  [bool](Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
}

function Wait-Until([scriptblock] $condition, [int] $seconds, [string] $what) {
  $deadline = (Get-Date).AddSeconds($seconds)
  while (-not (& $condition)) {
    if ((Get-Date) -gt $deadline) { throw "Timed out waiting for $what." }
    Start-Sleep -Seconds 3
  }
}

# 1. Emulator
& $adb start-server | Out-Null
$running = (& $adb devices) -match '^emulator-\d+\s+device$'
if (-not $running) {
  Write-Host 'Starting the Pixel 9 Pro emulator...' -ForegroundColor Cyan
  Start-Process $emulator -ArgumentList '-avd', $avd
  Wait-Until { (& $adb devices) -match '^emulator-\d+\s+device$' } 180 'the emulator to connect'
  Wait-Until { ((& $adb shell getprop sys.boot_completed) -join '').Trim() -eq '1' } 240 'the emulator to finish booting'
}
Write-Host 'Emulator is ready.' -ForegroundColor Green

# 2. Laravel backend (its own window; close it to stop the backend)
if (-not (Test-Port 8000)) {
  Write-Host 'Starting the Laravel backend on port 8000...' -ForegroundColor Cyan
  Start-Process powershell -WorkingDirectory (Join-Path $root 'backend-app') -ArgumentList '-NoExit', '-Command', 'php artisan serve --host 0.0.0.0'
  Wait-Until { Test-Port 8000 } 60 'the backend'
}
Write-Host 'Backend is running: http://localhost:8000/admin' -ForegroundColor Green

# 3. Expo dev server (its own window; close it to stop the dev server)
if (-not (Test-Port 8081)) {
  Write-Host 'Starting the Expo dev server on port 8081...' -ForegroundColor Cyan
  $expo = "Remove-Item Env:HTTP_PROXY,Env:HTTPS_PROXY,Env:http_proxy,Env:https_proxy -ErrorAction SilentlyContinue; `$env:NO_PROXY='localhost,127.0.0.1,::1'; npx expo start --port 8081"
  Start-Process powershell -WorkingDirectory (Join-Path $root 'mobile-react-native') -ArgumentList '-NoExit', '-Command', $expo
  Wait-Until { try { (Invoke-WebRequest 'http://127.0.0.1:8081/status' -UseBasicParsing -TimeoutSec 3).Content -match 'running' } catch { $false } } 180 'the Expo dev server'
}
Write-Host 'Expo dev server is running.' -ForegroundColor Green

# 4. Connect the emulator to the dev server and open MataKo
& $adb reverse tcp:8081 tcp:8081 | Out-Null
& $adb shell am start -n com.matako.mobile/.MainActivity | Out-Null
Write-Host ''
Write-Host 'MataKo is opening on the emulator. Keep the backend and Expo windows open while you use it.' -ForegroundColor Green
