<div id="toastcs"></div>
<?php
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        $toastTitle = $toast['title'] ?? 'Thông báo';
        $toastMessage = $toast['message'] ?? '';
        $toastType = $toast['type'] ?? 'info';
        $toastDuration = $toast['duration'] ?? 3000;

    
        unset($_SESSION['toast']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                toast({
                    title: " . json_encode($toastTitle) . ",
                    message: " . json_encode($toastMessage) . ",
                    type: " . json_encode($toastType) . ",
                    duration: ".$toastDuration."
                });
            });
        </script>";
}
?>
