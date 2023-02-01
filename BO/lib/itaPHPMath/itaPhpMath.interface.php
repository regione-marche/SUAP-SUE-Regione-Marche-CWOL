<?php
interface itaPhpMath{
    /**
     * Costruttore della funzione, prende in ingresso l'arrotondamento con cui vengono effettuate le operazioni
     * @param <int> $round cifre decimali oltre cui avviene l'arrotondamento
     */
    public function __construct($round=2);

    /**
     * Moltiplicazione
     * @param <int|float> $a primo operatore
     * @param <int|float> $b secondo operatore
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function mlt($a, $b, $r=null);

    /**
     * Divisione
     * @param <int|float> $a dividendo
     * @param <int|float> $b divisore
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function div($a, $b, $r=null);

    /**
     * Somma
     * @param <int|float> $a primo operatore
     * @param <int|float> $b secondo operatore
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function sum($a, $b, $r=null);
    
    /**
     * Sottrazione
     * @param <int|float> $a primo operatore
     * @param <int|float> $b secondo operatore
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function sub($a, $b, $r=null);

    /**
     * Restituisce il valore assoluto di un numero
     * @param <int|float> $a numero di cui si vuole il valore assoluto
     */
    public function abs($a);

    /**
     * Restituisce il modulo di un numero in un altro
     * @param <int|float> $a primo operatore
     * @param <int|float> $b secondo operatore
     */
    public function mod($a, $b);

    /**
     * Restituisce il primo operatore elevato a potenza di un'altro
     * @param <int|float> $a base
     * @param <int|float> $b potenza
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function pow($a, $b, $r=null);

    /**
     * Calcola la radice quadrata di un numero
     * @param <int|float> $a
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function sqrt($a, $r=null);
    
    /**
     * Calcola il numero arrotondato ad una data cifra decimale. Se non specificato usa quanto impostato nel costruttore
     * @param <int|float> $a
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function round($a, $r=null);

    /**
     * Risolve un'espressione matematica
     * @param <string> $expression espressione matematica sotto forma di stringa, accetta espressioni composte da:
     *                             (, ), +, -, *, /, ^, %
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function expression($expression, $r=null);
    
    /**
     * Calcola il numero arrotondato per difetto al decimale dato
     * @param <int|float> $a
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function floor($a, $r=null);
    
    /**
     * Calcola il numero arrotondato per eccesso al decimale dato
     * @param <int|float> $a
     * @param <int> $r (facoltativo) cifre decimali
     */
    public function ceil($a, $r=null);
}
?>