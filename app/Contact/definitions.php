<?php

use function DI\autowire;

return [
    'contact.to' => \DI\get('mail.to'),
    \App\Contact\ContactAction::class => autowire()->constructorParameter('to', \DI\get('contact.to'))
];
