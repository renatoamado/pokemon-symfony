<?php

use Pokemon\Models\Attack;
use Pokemon\Models\Card;
use Pokemon\Models\CardImages;
use Pokemon\Models\Resistance;
use Pokemon\Models\Weakness;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

function generateCard(string $id, string $name): Card
{
    $images = new CardImages();
    $images->setSmall('I\'m a small card');
    $images->setLarge('I\'m a large card');

    $weaknesses = new Weakness();
    $weaknesses->setType('Fire');
    $weaknesses->setValue('5');

    $resistances = new Resistance();
    $resistances->setType('Resistance');
    $resistances->setValue(5);

    $attacks = new Attack();
    $attacks->setName('Jungle Hammer');
    $attacks->setDamage('60');
    $attacks->setCost(['Grass', 'Grass', 'Colorless', 'Colorless']);
    $attacks->setConvertedEnergyCost('4');
    $attacks->setText('Heal 30 damage from this Pokémon.');

    $card = new Card();
    $card->setId($id);
    $card->setName($name);
    $card->setTypes(['Grass']);
    $card->setImages($images);
    $card->setWeaknesses([$weaknesses]);
    $card->setResistances([$resistances]);
    $card->setAttacks([$attacks]);

    return $card;
}