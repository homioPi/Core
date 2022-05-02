<?php 
    $succeeded = false;
    
    $device = @new \HomioPi\Devices\Device($card['parameters']['id']);

    if($device->setValue([
        'value'       => $card['parameters']['value'],
        'shown_value' => $card['parameters']['shown_value'],
        'force_set'   => true
    ]) === true) {
        $succeeded = true;
    } 

    echo($succeeded);
    return;
?>