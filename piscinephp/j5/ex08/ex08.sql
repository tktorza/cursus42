SELECT CONCAT(nom, ' ', prenom, ' ', DATE(date_naissance))
AS 'date de naissance' 
FROM fiche_personne
WHERE year(date_naissance) = 1989 
ORDER BY nom;