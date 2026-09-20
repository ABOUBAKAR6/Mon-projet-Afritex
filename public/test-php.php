<?php
// On récupère les clés depuis le fichier .env pour être sûr
$env = parse_ini_file('C:\wamp64\www\mon-projet\.env');
$secret_key = $env['FEDAPAY_SECRET_KEY'] ?? '';
$env_type = $env['FEDAPAY_ENV'] ?? 'live';

echo "<h1>Test API FedaPay Direct</h1>";
echo "<b>Clé utilisée :</b> " . substr($secret_key, 0, 10) . "...<br>";
echo "<b>Environnement :</b> " . $env_type . "<br><hr>";

$ch = curl_init("https://api.fedapay.com/v1/account");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secret_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)) {
    echo "<h2 style='color:red'>ERREUR CONNEXION cURL</h2>";
    echo "Détail : " . curl_error($ch);
} else {
    echo "<h2>Réponse du serveur FedaPay</h2>";
    echo "<b>Code HTTP :</b> " . $http_code . "<br>";
    echo "<b>Réponse brute :</b><pre>" . htmlspecialchars($response) . "</pre>";

    if ($http_code == 200) {
        echo "<h3 style='color:green'>✅ Votre clé API est VALIDE et votre compte est CONNECTÉ !</h3>";
    } elseif ($http_code == 401) {
        echo "<h3 style='color:red'>❌ Clé API INVALIDE. Vérifiez votre sk_live... dans le .env</h3>";
    } else {
        echo "<h3 style='color:orange'>⚠️ Le serveur a répondu, mais avec une erreur. Vérifiez votre compte sur le dashboard.</h3>";
    }
}
curl_close($ch);
?>