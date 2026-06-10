param(
    [string]$Url = 'http://127.0.0.1:8014',
    [string]$OutFile = "$env:TEMP\almeaa-local-page.png"
)

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$form = New-Object System.Windows.Forms.Form
$form.Width = 1440
$form.Height = 2400
$form.StartPosition = 'Manual'
$form.Location = New-Object System.Drawing.Point(-2000, -2000)

$browser = New-Object System.Windows.Forms.WebBrowser
$browser.ScriptErrorsSuppressed = $true
$browser.Width = 1400
$browser.Height = 2200
$browser.ScrollBarsEnabled = $true
$form.Controls.Add($browser)

$loaded = $false
$browser.add_DocumentCompleted({ $script:loaded = $true })
$browser.Navigate($Url)
$form.Show() | Out-Null

while (-not $loaded) {
    [System.Windows.Forms.Application]::DoEvents()
    Start-Sleep -Milliseconds 150
}

Start-Sleep -Seconds 2

$height = 2200
if ($browser.Document -and $browser.Document.Body) {
    $height = [Math]::Max(1200, [Math]::Min(4200, $browser.Document.Body.ScrollRectangle.Height + 40))
}

$browser.Height = $height
$bitmap = New-Object System.Drawing.Bitmap($browser.Width, $browser.Height)
$rect = New-Object System.Drawing.Rectangle(0, 0, $browser.Width, $browser.Height)
$browser.DrawToBitmap($bitmap, $rect)
$bitmap.Save($OutFile, [System.Drawing.Imaging.ImageFormat]::Png)
$bitmap.Dispose()
$form.Close()
$form.Dispose()

Write-Output $OutFile
