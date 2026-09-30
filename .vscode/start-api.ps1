# Start Laravel in the background if it is not already listening on port 8000.
$backendDirectory = Join-Path $PSScriptRoot '..\backend-app'
$backendDirectory = [System.IO.Path]::GetFullPath($backendDirectory)

function Test-LocalApiPort {
    $client = [System.Net.Sockets.TcpClient]::new()

    try {
        $connect = $client.BeginConnect('127.0.0.1', 8000, $null, $null)
        if (-not $connect.AsyncWaitHandle.WaitOne(300)) {
            return $false
        }

        $client.EndConnect($connect)
        return $client.Connected
    }
    catch {
        return $false
    }
    finally {
        $client.Dispose()
    }
}

if (-not (Test-LocalApiPort)) {
    $phpPath = (Get-Command php -ErrorAction Stop).Source
    Start-Process `
        -FilePath $phpPath `
        -ArgumentList @('artisan', 'serve', '--host=0.0.0.0') `
        -WorkingDirectory $backendDirectory `
        -WindowStyle Hidden | Out-Null
}

$deadline = (Get-Date).AddSeconds(20)
while (-not (Test-LocalApiPort) -and (Get-Date) -lt $deadline) {
    Start-Sleep -Milliseconds 500
}

if (-not (Test-LocalApiPort)) {
    throw 'Laravel did not start on port 8000. Check PHP and the backend-app folder.'
}
