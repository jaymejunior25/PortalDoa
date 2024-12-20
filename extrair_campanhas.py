import json
import requests
from bs4 import BeautifulSoup

# URL do conteúdo HTML
url = "https://www.hemopa.pa.gov.br/site/noticias/acompanhe-nossas-campanhas-externas-marco/"

# Faz uma requisição GET para a URL
response = requests.get(url)

# Verifica se a requisição foi bem-sucedida
if response.status_code == 200:
    # Cria o objeto BeautifulSoup para analisar o HTML
    soup = BeautifulSoup(response.content, 'html.parser')
    
    # Encontra o artigo específico onde estão as campanhas
    article = soup.find('article', class_='post-3054')
    
    if article:
        # Extrai e organiza as informações das campanhas
        campaigns = []
        for p in article.find_all('p'):
            campaign_info = {}
            strong_tag = p.find('strong')

            if strong_tag:
                campaign_info['Nome'] = strong_tag.text.strip()
                text = p.get_text(separator='\n').splitlines()
                for line in text:
                    if "Data:" in line:
                        campaign_info['Data'] = line.replace("Data:", "").strip()
                    elif "Local:" in line:
                        campaign_info['Local'] = line.replace("Local:", "").strip()
                    elif "Hora:" in line or "Horário:" in line:
                        campaign_info['Hora'] = line.replace("Hora:", "").replace("Horário:", "").strip()
                    elif "Previsão:" in line:
                        campaign_info['Previsão de Voluntários'] = line.replace("Previsão:", "").strip()
                    elif "Infraestrutura:" in line:
                        campaign_info['Infraestrutura'] = line.replace("Infraestrutura:", "").strip()
                
                campaigns.append(campaign_info)

        # Salva as campanhas em um arquivo JSON
        with open('campanhas.json', 'w', encoding='utf-8') as f:
            json.dump(campaigns, f, ensure_ascii=False, indent=4)

        print("Campanhas extraídas e salvas em 'campanhas.json'.")
    else:
        print("Artigo não encontrado.")
else:
    print(f"Erro ao acessar a página: {response.status_code}")
