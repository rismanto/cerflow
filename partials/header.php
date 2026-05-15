<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - CER Flow" : "CER Flow"; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
    <style>
        * { border-radius: 0 !important; }
        body { font-size: 14px; }
    </style>
</head>
<body class="bg-stone-100 min-h-screen flex flex-col font-sans text-slate-800">
