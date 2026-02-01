<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج كرة القدم المباشرة</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>LIVE FOOTBALL</h1>
            <div class="subtitle">نتائج المباريات المباشرة</div>
        </header>

        <div class="controls">
            <select id="competitionSelect">
                <option value="">اختر المسابقة</option>
                <option value="PL">الدوري الإنجليزي</option>
                <option value="PD">الدوري الإسباني</option>
                <option value="BL1">الدوري الألماني</option>
                <option value="SA">الدوري الإيطالي</option>
                <option value="FL1">الدوري الفرنسي</option>
                <option value="CL">دوري أبطال أوروبا</option>
            </select>
        </div>

        <div class="tabs">
            <button class="tab active" data-view="matches">المباريات</button>
            <button class="tab" data-view="standings">الترتيب</button>
        </div>

        <div id="content"></div>
    </div>

    <footer>
        <p>Powered by <a href="https://www.football-data.org" target="_blank">football-data.org</a></p>
    </footer>

    <script>
        const API_ENDPOINT = 'api.php';

        const competitionSelect = document.getElementById('competitionSelect');
        const content = document.getElementById('content');
        const tabs = document.querySelectorAll('.tab');

        let currentView = 'matches';
        let currentCompetition = '';

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentView = tab.dataset.view;
                if (currentCompetition) loadData();
            });
        });

        competitionSelect.addEventListener('change', e => {
            currentCompetition = e.target.value;
            if (currentCompetition) loadData();
            else content.innerHTML = '';
        });

        async function loadData() {
            content.innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const params = new URLSearchParams({
                    competition: currentCompetition,
                    endpoint: currentView
                });

                const res = await fetch(`${API_ENDPOINT}?${params}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json();

                if (data.error) throw new Error(data.error);

                if (currentView === 'matches') {
                    displayMatches(data.matches || []);
                } else {
                    displayStandings(data.standings || []);
                }
            } catch (err) {
                content.innerHTML = `<div class="error">فشل التحميل: ${err.message}</div>`;
                console.error(err);
            }
        }

        function displayMatches(matches) {
            if (!matches.length) {
                content.innerHTML = '<div class="error">لا توجد مباريات متاحة</div>';
                return;
            }

            matches.sort((a,b) => new Date(b.utcDate) - new Date(a.utcDate));
            const recent = matches.slice(0, 20);

            content.innerHTML = `
                <div class="matches-grid">
                    ${recent.map(createMatchCard).join('')}
                </div>
            `;
        }

        function displayStandings(standings) {
            if (!standings.length || !standings[0]?.table?.length) {
                content.innerHTML = '<div class="error">لا يوجد ترتيب متاح</div>';
                return;
            }

            const table = standings[0].table;

            content.innerHTML = `
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th>المركز</th>
                            <th>الفريق</th>
                            <th>لعب</th>
                            <th>ف</th>
                            <th>ت</th>
                            <th>خ</th>
                            <th>له</th>
                            <th>عليه</th>
                            <th>±</th>
                            <th>النقاط</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${table.map(row => `
                            <tr>
                                <td class="position">${row.position}</td>
                                <td>
                                    ${row.team.crest ? `<img src="${row.team.crest}" class="team-crest" alt="">` : ''}
                                    ${row.team.name}
                                </td>
                                <td>${row.playedGames}</td>
                                <td>${row.won}</td>
                                <td>${row.draw}</td>
                                <td>${row.lost}</td>
                                <td>${row.goalsFor}</td>
                                <td>${row.goalsAgainst}</td>
                                <td>${row.goalDifference}</td>
                                <td><strong>${row.points}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        function createMatchCard(match) {
            const status = getStatus(match.status);
            const date = new Date(match.utcDate);
            const homeScore = match.score.fullTime.home ?? '-';
            const awayScore = match.score.fullTime.away ?? '-';

            return `
                <div class="match-card">
                    <div class="competition-badge">${match.competition.name}</div>
                    <div class="match-status status-${status.class}">${status.text}</div>
                    <div class="match-teams">
                        <div class="team-row">
                            <div class="team-name">${match.homeTeam.name}</div>
                            <div class="team-score">${homeScore}</div>
                        </div>
                        <div class="team-row">
                            <div class="team-name">${match.awayTeam.name}</div>
                            <div class="team-score">${awayScore}</div>
                        </div>
                    </div>
                    <div class="match-info">
                        <span>${date.toLocaleDateString('ar-SA')}</span>
                        <span>${date.toLocaleTimeString('ar-SA', {hour:'2-digit', minute:'2-digit'})}</span>
                    </div>
                </div>
            `;
        }

        function getStatus(status) {
            switch(status) {
                case 'IN_PLAY':
                case 'PAUSED':   return { text: 'مباشر', class: 'live' };
                case 'FINISHED': return { text: 'انتهت', class: 'finished' };
                default:         return { text: 'مجدولة', class: 'scheduled' };
            }
        }
    </script>
</body>
</html>
