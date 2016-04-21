SELECT nom, prenom
FROM fiche_personne
WHERE nom like "%-%" OR prenom like "%-%"
ORDER BY nom asc, prenom asc;
