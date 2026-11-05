from pathlib import Path
import json
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

ROOT=Path(__file__).resolve().parents[2]
data=json.loads((ROOT/'evidence/raw/benchmark-summary.json').read_text())
out=ROOT/'evidence/charts';out.mkdir(parents=True,exist_ok=True)
plt.rcParams.update({'font.family':'DejaVu Sans','font.size':11,'axes.spines.top':False,'axes.spines.right':False})
fig,axes=plt.subplots(1,2,figsize=(10.8,4.2),layout='constrained')
for transport,color in [('polling','#a497c4'),('websocket','#6351b7')]:
 runs=[r for r in data['runs'] if r['transport']==transport]
 xs=[r['run']+(-.17 if transport=='polling' else .17) for r in runs]
 axes[0].bar(xs,[r['medianMs'] for r in runs],width=.3,color=color,label=transport.capitalize())
 axes[1].bar(xs,[r['p95Ms'] for r in runs],width=.3,color=color,label=transport.capitalize())
for ax,title in zip(axes,['Trung vị độ trễ quan sát','p95 độ trễ theo hạng gần nhất']):
 ax.set_title(title,loc='left',fontsize=12,pad=14);ax.set_ylabel('Mili giây');ax.set_xlabel('Lượt đo');ax.set_xticks([1,2,3]);ax.grid(axis='y',alpha=.12);ax.set_axisbelow(True)
axes[0].legend(frameon=False,loc='upper right')
fig.suptitle('So sánh kênh nhận trên máy local',x=.03,ha='left',fontsize=16,fontweight='bold')
fig.savefig(out/'delivery-delay.png',dpi=180);plt.close(fig)
summary=[]
for transport in ['polling','websocket']:
 runs=[r for r in data['runs'] if r['transport']==transport]
 summary.append({'transport':transport,'medianRangeMs':[min(r['medianMs'] for r in runs),max(r['medianMs'] for r in runs)],'p95RangeMs':[min(r['p95Ms'] for r in runs),max(r['p95Ms'] for r in runs)],'received':sum(r['received'] for r in runs),'sent':sum(r['sent'] for r in runs),'httpRequests':sum(r['httpRequests'] for r in runs)})
(out/'summary.json').write_text(json.dumps(summary,indent=2),encoding='utf-8')
print(json.dumps(summary,indent=2))
