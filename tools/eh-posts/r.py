import base64,json
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
T=[
{"t":"मुख्यमंत्री ग्राम परिवहन योजना 2026","c":"योजना","p":["प्रदेश की सभी 59,163 ग्राम पंचायतों को बस/परिवहन सेवा से जोड़ा जाएगा।","इसमें 15 से 28 सीट वाले निजी वाहन शामिल होंगे।","वाहन डीज़ल, CNG या इलेक्ट्रिक — तीनों तरह के चल सकेंगे।","वाहन की अधिकतम आयु पंजीकरण तिथि से 8 वर्ष तक होगी।"],"k":"59,163","kl":"ग्राम पंचायतें जुड़ेंगी"},
{"t":"नवयुग पालिका योजना","c":"योजना","p":["यह स्मार्ट सिटी मिशन का विस्तारित रूप है।","ऐसे 58 जिला मुख्यालय, जहाँ नगर निगम नहीं है (55 नगर पालिका + 3 नगर पंचायत)।","इन्हें डिजिटल गवर्नेंस और आधुनिक सुविधाओं से जोड़ा जाएगा।","5 वर्षों में पूरा ₹2,916 करोड़ राज्य सरकार खर्च करेगी।"],"k":"58","kl":"जिला मुख्यालय · ₹2,916 करोड़"},
{"t":"कालिंजर किला — राष्ट्रीय भू-धरोहर स्थल","c":"विरासत","p":["भारतीय भूवैज्ञानिक सर्वेक्षण (GSI) ने घोषणा की।","तारीख — 16 मार्च 2026।","बांदा जिले का ऐतिहासिक कालिंजर किला और आसपास का पहाड़ी क्षेत्र शामिल।","यहाँ की चट्टानें लगभग 2.5 बिलियन (250 करोड़) वर्ष पुरानी हैं।"],"k":"बांदा","kl":"National Geo Heritage Site"},
{"t":"लक्ष्मण एवं रानी लक्ष्मीबाई पुरस्कार","c":"खेल","p":["प्रदेश के सर्वोच्च खेल सम्मान की राशि ₹3.11 लाख से बढ़ाकर ₹10 लाख की गई।","5 पुरुष खिलाड़ियों को लक्ष्मण पुरस्कार — जैसे उत्तम सिंह (हॉकी), प्रवीण कुमार (पैरा-एथलेटिक्स)।","4 महिला खिलाड़ियों को रानी लक्ष्मीबाई पुरस्कार — जैसे वंतिका अग्रवाल (शतरंज)।","क्रिकेटर रिंकू सिंह 'क्षेत्रीय क्रीड़ा अधिकारी' नियुक्त।"],"k":"₹10 लाख","kl":"नई पुरस्कार राशि"},
{"t":"वाराणसी का गिनीज़ वर्ल्ड रिकॉर्ड","c":"पर्यावरण","p":["वाराणसी नगर निगम ने यह रिकॉर्ड बनाया।","सिर्फ एक घंटे में 2,51,446 पौधे लगाए गए।","स्थान — सुजाबाद-डोमरी, गंगा किनारे 350 बीघा ज़मीन।","चीन का 2018 का रिकॉर्ड टूटा।"],"k":"2,51,446","kl":"पौधे — सिर्फ 1 घंटे में"},
]
css=f"""<style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1280px;height:720px;font-family:B;background:#fff;overflow:hidden;position:relative}}
.bg{{position:absolute;right:-120px;top:-160px;width:460px;height:460px;border-radius:50%;background:#e0efe9}}
.top{{position:absolute;left:48px;top:30px;display:flex;gap:10px;align-items:center}}
.pill{{background:#ec8516;color:#fff;font-weight:700;font-size:18px;padding:5px 18px;border-radius:30px}}
.pill2{{background:#e0efe9;color:#015a3e;font-weight:700;font-size:18px;padding:5px 18px;border-radius:30px}}
.brand{{position:absolute;right:48px;top:30px;font-weight:700;font-size:22px;color:#015a3e}}
h1{{position:absolute;left:48px;top:78px;width:1180px;margin:0;font-size:44px;line-height:1.25;color:#013d2b}}
.pts{{position:absolute;left:48px;top:185px;width:720px}}
.pt{{display:flex;gap:16px;align-items:flex-start;margin-bottom:22px;font-size:25px;line-height:1.45;color:#13201c}}
.d{{flex:0 0 30px;height:30px;border-radius:50%;background:#015a3e;color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:4px}}
.pt:nth-child(even) .d{{background:#ec8516}}
.key{{position:absolute;left:820px;top:185px;width:412px;height:430px;border-radius:28px;background:#015a3e;padding:30px 32px;box-shadow:0 10px 30px rgba(1,90,62,.25);overflow:hidden}}
.kh{{color:#ffdb99;font-size:22px;font-weight:700}} .kv{{color:#fff;font-size:68px;font-weight:700;line-height:1.1;margin-top:60px}}
.kl{{color:#e0efe9;font-size:24px;margin-top:18px}} .ring{{position:absolute;right:-50px;bottom:-50px;width:170px;height:170px;border-radius:50%;border:22px solid #ec8516;opacity:.9}}
.foot{{position:absolute;left:0;right:0;bottom:0;height:62px;background:#015a3e;color:#fff;font-size:24px;font-weight:700;display:flex;align-items:center;justify-content:space-between;padding:0 48px}}
.foot span{{color:#ffdb99}}</style>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":720},device_scale_factor=1.5)
    for i,t in enumerate(T,1):
        pts="".join(f"<div class=pt><div class=d>{k+1}</div><div>{x}</div></div>" for k,x in enumerate(t["p"]))
        h=css+f"<div class=bg></div><div class=top><span class=pill>{t['c']}</span><span class=pill2>मार्च 2026 · UP करेंट अफेयर्स</span></div><div class=brand>📚 Exam Hub</div><h1>{t['t']}</h1><div class=pts>{pts}</div><div class=key><div class=kh>📌 याद रखें</div><div class=kv>{t['k']}</div><div class=kl>{t['kl']}</div><div class=ring></div></div><div class=foot><div>ज्वाइन 👉 <span>@examhub2026</span></div><div>UPSSSC · UPPSC · सभी परीक्षाओं के लिए</div></div>"
        pg.set_content("<meta charset=utf-8>"+h); pg.wait_for_timeout(250); pg.screenshot(path=f"eh-ca-{i}.jpg",type="jpeg",quality=92)
    b.close()
json.dump(T,open('t.json','w'),ensure_ascii=False)
