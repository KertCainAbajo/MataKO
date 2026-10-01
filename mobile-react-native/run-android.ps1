$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

# Prevent the local Expo/Metro requests from going through the stale proxy.
foreach ($proxyName in @('HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy')) {
  Remove-Item "Env:$proxyName" -ErrorAction SilentlyContinue
}
$env:NO_PROXY = 'localhost,127.0.0.1,::1'
$env:no_proxy = $env:NO_PROXY

$connectedEmulator = (& adb devices) | Select-String '^emulator-\d+\s+device$'
if (-not $connectedEmulator) {
  Write-Host 'Start your Pixel emulator first (Android Studio > Tools > Device Manager), then run this command again.' -ForegroundColor Yellow
  exit 1
}

# Pick an unused Metro port so an unrelated process cannot block Expo.
$port = 8087
while ($true) {
  $probe = [System.Net.Sockets.TcpClient]::new()
  try {
    $probe.Connect('127.0.0.1', $port)
    $port++
  } catch {
    break
  } finally {
    $probe.Dispose()
  }
}

Write-Host "Starting Expo on port $port in this VS Code terminal. Keep it open while using the app."
& adb reverse "tcp:$port" "tcp:$port" | Out-Null
Write-Host 'When Metro says it is ready, open another VS Code terminal and run this to open MataKo in the emulator:' -ForegroundColor Green
Write-Host "adb shell am start -a android.intent.action.VIEW -d exp://127.0.0.1:$port" -ForegroundColor Cyan
npx expo start --clear --host lan --port $port
