# Полные списки доменов и поддоменов (сервер 89.169.39.244)

Актуально по состоянию nginx + certbot на сервере.

---

## По сертификатам (Let's Encrypt)

| Сертификат (Certificate Name) | Домены и поддомены в сертификате |
|------------------------------|-----------------------------------|
| **admin.neeklo.ru** | admin.neeklo.ru |
| **api.siteaccess.ru** | api.siteaccess.ru |
| **auto.siteaccess.ru** | auto.siteaccess.ru, www.auto.siteaccess.ru |
| **essens-store.ru** | essens-store.ru |
| **file-to-text.siteaacess.ru** | file-to-text.siteaacess.ru |
| **insales.siteaccess.ru** | insales.siteaccess.ru |
| **neekloai.ru** | neekloai.ru, www.neekloai.ru |
| **online.siteaccess.ru** | online.siteaccess.ru |
| **p-d-a-b.neeklo.ru** | p-d-a-b.neeklo.ru |
| **parser-auto.siteaccess.ru** | parser-auto.siteaccess.ru |
| **proffi-center.ru** | proffi-center.ru, www.proffi-center.ru, **anapa.proffi-center.ru**, **stavropol.proffi-center.ru**, **moscow.proffi-center.ru** |
| **trendagent.siteaccess.ru** | trendagent.siteaccess.ru |

---

## Единый список всех доменов и поддоменов (по сертификатам)

- admin.neeklo.ru  
- api.siteaccess.ru  
- auto.siteaccess.ru  
- www.auto.siteaccess.ru  
- essens-store.ru  
- file-to-text.siteaacess.ru  
- insales.siteaccess.ru  
- neekloai.ru  
- www.neekloai.ru  
- online.siteaccess.ru  
- p-d-a-b.neeklo.ru  
- parser-auto.siteaccess.ru  
- proffi-center.ru  
- www.proffi-center.ru  
- anapa.proffi-center.ru  
- stavropol.proffi-center.ru  
- moscow.proffi-center.ru  
- trendagent.siteaccess.ru  

**Итого: 18 имён (доменов/поддоменов).**

---

## Дополнительно в Nginx (не в отдельном сертификате)

- **89.169.39.244** — IP сервера (default / служебные виртуальные хосты)  
- **_**(default_server)** — fallback server  

---

## Proffi Center — привязка поддоменов к HTTPS

Поддомены **anapa**, **stavropol**, **moscow** привязаны к тому же сертификату и тому же приложению, что и **proffi-center.ru**:

- https://proffi-center.ru/  
- https://www.proffi-center.ru/  
- https://anapa.proffi-center.ru/  
- https://stavropol.proffi-center.ru/  
- https://moscow.proffi-center.ru/  

Конфиг: `/etc/nginx/sites-available/proffi-center.ru` (образец в репозитории: `tools/deploy/proffi-center.ru.nginx.conf`).
