<?php
if (!isset($pageTitle)) {
          $pageTitle = 'My Club Hub Admin';
}

$additionalHeadContent = $additionalHeadContent ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title><?= htmlspecialchars($pageTitle); ?></title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
          <!-- Replace this (CSS CDN) -->
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
          <!-- With your Font Awesome Kit (set your kit ID) -->
          <script src="https://kit.fontawesome.com/79f3c02ae0.js" crossorigin="anonymous"></script>
          <script src="https://cdn.tailwindcss.com"></script>
          <script>
                    tailwind.config = {
                              theme: {
                                        extend: {
                                                  colors: {
                                                            maroon: '#000814',
                                                            burgundy: '#001d3d',
                                                            gold: '#ffc300',
                                                            cream: '#f6f8ff',
                                                            charcoal: '#003566',
                                                            teal: '#ffd60a',
                                                  },
                                                  fontFamily: {
                                                            sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                                                  },
                                                  boxShadow: {
                                                            'maroon-lg': '0 35px 70px -30px rgba(0,13,61,0.45)',
                                                            'gold-soft': '0 30px 60px -40px rgba(255,195,0,0.5)',
                                                  }
                                        }
                              }
                    };
          </script>
          <?= $additionalHeadContent ?>
</head>

<body class="min-h-screen bg-maroon font-sans text-cream">
          <div class="flex min-h-screen">
