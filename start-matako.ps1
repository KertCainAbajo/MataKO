# Starts everything MataKo needs after the laptop restarts:
# the Android emulator, the Laravel backend, the Expo dev server, the MataKo app and the admin page.
# Run from PowerShell:  powershell -ExecutionPolicy Bypass -File D:\MataKO\MataKO\start-matako.ps1
# or double-click the "Start MataKo" shortcut on the desktop.

# Native tools like adb print normal progress to stderr, so only our own checks stop the script.
$ErrorActionPreference = 'Continue'
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

function Test-Emulator {
  [bool]((& $adb devices) -match '^emulator-\d+\s+device$')
}

try {
  # 1. Emulator. Restart adb first, because a stuck adb makes every later step hang.
  Write-Host 'Resetting adb...' -ForegroundColor Cyan
  Get-Process adb -ErrorAction SilentlyContinue | Stop-Process -Force
  cmd /c ""$adb" start-server >nul 2>&1"

  $emulatorRunning = [bool](Get-Process qemu-system-x86_64 -ErrorAction SilentlyContinue)
  if (-not $emulatorRunning) {
    Write-Host 'Starting the Pixel 9 Pro emulator (this can take 1-2 minutes)...' -ForegroundColor Cyan
    Start-Process $emulator -ArgumentList '-avd', $avd
  }
  Wait-Until { Test-Emulator } 240 'the emulator to connect'
  Wait-Until { ((& $adb shell getprop sys.boot_completed) -join '').Trim() -eq '1' } 300 'the emulator to finish booting'
  Write-Host 'Emulator is ready.' -ForegroundColor Green

  # 2. Laravel backend (its own window; close it to stop the backend)
  if (-not (Test-Port 8000)) {
    Write-Host 'Starting the Laravel backend on port 8000...' -ForegroundColor Cyan
    Start-Process powershell -WorkingDirectory (Join-Path $root 'backend-app') -ArgumentList '-NoExit', '-Command', '$Host.UI.RawUI.WindowTitle = ''MataKo backend - keep open''; php artisan serve --host 0.0.0.0'
    Wait-Until { Test-Port 8000 } 60 'the backend'
  }
  Write-Host 'Backend is running.' -ForegroundColor Green

  # 3. Expo dev server (its own window; close it to stop the dev server)
  if (-not (Test-Port 8081)) {
    Write-Host 'Starting the app dev server on port 8081...' -ForegroundColor Cyan
    $expo = "`$Host.UI.RawUI.WindowTitle = 'MataKo app server - keep open'; Remove-Item Env:HTTP_PROXY,Env:HTTPS_PROXY,Env:http_proxy,Env:https_proxy -ErrorAction SilentlyContinue; `$env:NO_PROXY='localhost,127.0.0.1,::1'; npx expo start --port 8081"
    Start-Process powershell -WorkingDirectory (Join-Path $root 'mobile-react-native') -ArgumentList '-NoExit', '-Command', $expo
  }
  Wait-Until { try { (Invoke-WebRequest 'http://127.0.0.1:8081/status' -UseBasicParsing -TimeoutSec 3).RawContent -match 'packager-status:running' } catch { $false } } 180 'the app dev server'
  Write-Host 'App dev server is running.' -ForegroundColor Green

  # 4. Connect the emulator to the dev server, open MataKo and the admin page
  & $adb reverse tcp:8081 tcp:8081 | Out-Null
  # The app reaches the backend through this tunnel; the emulator's own route cuts off larger downloads like pictures.
  & $adb reverse tcp:8000 tcp:8000 | Out-Null
  & $adb shell am force-stop com.matako.mobile | Out-Null
  & $adb shell am start -n com.matako.mobile/.MainActivity | Out-Null
  Start-Process 'http://localhost:8000/admin'

  Write-Host ''
  Write-Host 'All set!' -ForegroundColor Green
  Write-Host '  - MataKo is opening on the emulator (the first load can take up to a minute).'
  Write-Host '  - The admin dashboard opened in your browser: http://localhost:8000/admin'
  Write-Host '  - Keep the "MataKo backend" and "MataKo app server" windows open while you work.'
} catch {
  Write-Host ''
  Write-Host "Something went wrong: $($_.Exception.Message)" -ForegroundColor Red
  Write-Host 'Try running this again. If it keeps failing, restart the laptop and run it once more.' -ForegroundColor Yellow
}

Write-Host ''
Read-Host 'Press Enter to close this window'
