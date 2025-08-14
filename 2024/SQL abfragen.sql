use lap_einkaufsliste;

-- aufgabe 4a
select benutzer.BENUTZERMail, liste.LISTEBezeichnung, count(liste_produkt.PRODUKT_idPRODUKT) as anzahlProdukte, COUNT(case when liste_produkt.LISTE_PRODUKTGekauft = 1 THEN 1 ELSE NULL END) as gekauft, COUNT(case when liste_produkt.LISTE_PRODUKTGekauft = 0 THEN 1 ELSE NULL END) as nichtGekauft from liste join liste_produkt on liste_produkt.LISTE_idListe = liste.idListe
join benutzer on benutzer.idBENUTZER = liste.BENUTZER_idBENUTZER
group by benutzer.BENUTZERMail;


-- aufgabe 4b
select idLISTE_PRODUKT, liste.idLISTE from liste_produkt 
join liste on liste.idListe = liste_produkt.LISTE_idListe
join produkt on produkt.idProdukt = liste_produkt.PRODUKT_idProdukt
where liste.BENUTZER_idBENUTZER <> produkt.BENUTZER_idBENUTZER;

select produkt.idPRODUKT, kategorie.idKATEGORIE from produkt
join kategorie on kategorie.idKATEGORIE = produkt.KATEGORIE_idKATEGORIE
where produkt.BENUTZER_idBENUTZER <> kategorie.BENUTZER_idBENUTZER;

select shop.idSHOP, produkt.idPRODUKT from shop
join liste_produkt on liste_produkt.SHOP_idSHOP = shop.idSHOP
join produkt on produkt.idPRODUKT = liste_produkt.PRODUKT_idPRODUKT
where produkt.BENUTZER_idBENUTZER <> shop.BENUTZER_idBENUTZER;