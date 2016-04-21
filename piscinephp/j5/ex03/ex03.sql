INSERT INTO ft_table (login, groupe, date_de_creation) SELECT nom, "other", date_naissance 
FROM fiche_personne WHERE length(nom) < 9 && nom LIKE '%a%' ORDER BY nom ASC LIMIT 10;