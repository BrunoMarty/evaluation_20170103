<?php
$temps = date("H:i");     // variable temps
$lines=file('questions.qs');     //  ouverture du fichier questions.qs
foreach ($lines as $lineNumber => $lineContent)    // navigation dans le fichier questions.qs
{
    if (substr_count($lineContent,'#')==2)      // on teste si c'est un theme
        {$tab_theme[]=$lineContent;}
    else if(substr_count($lineContent,'#')==1){   // on teste si c'est une question
      $tab_question["texte"][]=$lineContent;
      $tab_question["theme"][]=count($tab_theme)-1;
    }
     else if(preg_match_all("#^\-#", trim($lineContent))){  // on teste si c'est une option du qcm
        $tab_qcm["texte"][] = $lineContent;
        $tab_qcm["question"][] = count($tab_question["texte"])-1;
      }
}
$index = fopen("index.html","w+");    // on cree un fichier index.html
fputs($index,"<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
<title>evaluations du 03 janvier 2017 – ville</title>
<script src=\"java.js\"></script><script src=\"https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">
</head><body><h1>Evaluations du 03 Janviers 2017</h1><form action=\"evaluation.php\" method=\"POST\" >"); // on ajoute le debut de la syntaxe html
for($i=0;$i<count($tab_theme);$i++)   // navigation dans le tableau theme
{
  fputs($index,"<section><h3>".  str_replace("##", "", $tab_theme[$i]) ."</h3>");
  for($j=0;$j<count($tab_question["texte"]);$j++)   // navigation dans le tableau question
  {
        $ul = false;
        $test_qcm=false;  // variable pour savoir si on doit creer un textarea ou une liste radio
        if ($tab_question["theme"][$j]==$i)   // test si la question appartient au theme i
           {
                $nb = $j +1;
                fputs($index,"<article>".$nb.")".str_replace("#", "", $tab_question["texte"][$j])."<br>");  // ajout de la question dans l'index.html
                if(in_array($j,$tab_qcm["question"])){
                    fputs($index,"<ul>");
                    $ul = true;
                }
                for($k=0;$k<count($tab_qcm["texte"]);$k++){   // navigation dans le tableau avec les options qcm
                    if ($tab_qcm["question"][$k]==$j){    // test si l'option qcm appartient a la queston j
                        fputs($index, '<li><input type="radio" onclick="mafonction(\'h'.$j.'\')" name="' . $j . '" value="' . $tab_qcm["texte"][$k] . '">' . str_replace("-", "", $tab_qcm["texte"][$k]) . '</li>');
                        $test_qcm = true;  // on ne devra pas creer un textarea
                        }
                }
                if($ul)
                  fputs($index,"</ul>");
                if($test_qcm==false)  // test de la variable pour creer une textarea ou pas
                {fputs($index, '</br><textarea rows="4" onkeyup="mafonction(\'h'.$j.'\')" name="' . $j. '" cols="50"></textarea>');}  // ajout de la textarea dans l'index.html

                fputs($index, "<input type=\"hidden\" name=\"h".$j."\" value=\"0\"></article>");
          }
    }
    fputs($index,"</section><br>");  // fermeture de la section (theme)
}
fputs($index,"<INPUT TYPE=\"submit\" NAME=\"bouton\" VALUE=\"Valider\"></form></body>");  // fermeture des balises du html
if(!empty($_POST)){
    $resultat = fopen("resultat.xml","w+");   // on cree le fichier resultat.xml
    chmod("resultat.xml",0777);
    fwrite($resultat,  '<?xml version="1.0" encoding="UTF-8"?>');   // permet de lire les accents etc ...
    for($i=0;$i<count($tab_question["texte"]);$i++)     // boucle qui fait autant de tour qu'il y a de questions (donc autant de réponses)
      {
        $nb= $i+1;
        fputs($resultat,"<Question".$nb." Temps:".$_POST["h".$i].">".$_POST[$i]."</Question".$nb.">");  // on recupere les reponses en POST et on les rajoutes dans le xml
      }
}
