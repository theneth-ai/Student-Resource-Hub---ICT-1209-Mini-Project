const modules = [
    [1, "ICT 1202", "Electronic Circuits", 0], [1, "ICT 1305", "Program Designing and Programming", 0], [1, "ICT 1111", "Productivity and Collaborative Tools", 0], [1, "CMT 1301", "Fundamentals of Physics for Technology", 0], [1, "CMT 1303", "Fundamentals of Mathematics for Technology", 0], [1, "CML 1301", "Personality Development", 0], [1, "CMT 1005", "Communication Skills I", 0], [1, "ICT 1210", "Introduction to Multimedia", 0], [1, "ICT 1108", "Skill Development Project I", 0], [1, "ICT 1209", "Web Technologies", 1], [1, "ICT 1207", "Human Computer Interaction", 1], [1, "CML 1203", "Principles of Management", 0], [1, "CML 1204", "Health and Wellbeing", 0], [1, "CMT 1009", "Communication Skills II", 0], [1, "CMT 1307", "Mathematics For Technology I", 0], [1, "ENT 1302", "Fundamentals of Electricity and Magnetism", 0],
    [2, "ICT 2202", "Operating Systems", 0], [2, "ICT 2303", "Data Structures and Algorithms", 0], [2, "ICT 2304", "Object Oriented Programming", 0], [2, "ICT 2207", "Software System Design", 0], [2, "ICT 2212", "Skill Development Project II", 0], [2, "CML 2202", "Engineering Economics", 0], [2, "CMT 2002", "Communication Skills III", 0], [2, "EET 2207", "Mathematics for Technology II", 0], [2, "ICT 2305", "Computational Mathematics", 0], [2, "ICT 2214", "Introduction to Information Systems", 0], [2, "ICT 2211", "Fundamentals of Statistics", 0], [2, "ICT 2213", "Data Communication and Networking", 0], [2, "ICT 2308", "Database Systems", 0], [2, "ICT 2109", "Communication and Learning Skills", 0], [2, "CML 2204", "Foreign Language", 0], [2, "CML 2205", "Ethics for Science and Technology", 0],
    [3, "ICT 3201", "Software Project Management", 1], [3, "ICT 3203", "Scientific Computer Applications", 0], [3, "CML 3101", "Legal and Patent Aspects", 0], [3, "ICT 3312", "Software Verification and Validation", 0], [3, "ICT 3206", "Skills Development Project III", 0], [3, "ICT 3314", "Advanced Computer Networks", 0], [3, "ICT 3208", "Design and Analysis of Algorithms", 0], [3, "ICT 3307", "Computational Statistics", 0], [3, "ICT 3217", "Advance Computer Networks", 0], [3, "ICT 3209", "Computer Organization and Architecture", 0], [3, "ICT 3310", "Information Security", 0], [3, "ICT 3311", "Robotics", 0], [3, "ICT 3315", "Internet of Things", 0], [3, "ICT 3213", "Advanced SW System Design", 0], [3, "ICT 3216", "Research Methodology", 0], [3, "ICT 3204", "E-Business Systems", 0], [3, "CML 3203", "Basics of Accountancy", 0],
    [4, "ICT 4301", "Mobile Computing", 0], [4, "ICT 4202", "Internet Applications", 0], [4, "ICT 4203", "Software Engineering", 0], [4, "ICT 4205", "Current Topics in Information Technology", 0], [4, "ICT 4306", "Data Science", 1], [4, "ICT 4207", "Artificial Intelligence", 0], [4, "ICT 4210", "Digital Image Processing", 0], [4, "ICT 4211", "Computer Graphics and Visualization", 0], [4, "CML 4201", "Entrepreneurship", 0], [4, "CML 4202", "Human Resource Management", 0]
];

document.addEventListener("DOMContentLoaded", function () {

    let cardContainer = document.getElementById('cardContainer');
    let searchInput = document.getElementById('searchInput');
    let yearFilter = document.getElementById('yearFilter');
    let categoryTitle = document.getElementById('categoryTitle');

    if (cardContainer && searchInput && yearFilter) {
        function renderCards() {
            let selectedYear = yearFilter.value;
            let searchText = searchInput.value.toLowerCase().trim();
            let isDefaultState = (selectedYear === "All" && searchText === "");

            if (categoryTitle) {
                categoryTitle.innerText = isDefaultState ? "Popular Categories:" : "Search Results:";
            }

            let displayModules = [];

            if (isDefaultState) {
                let recentCodes = (window.RECENT_MODULE_CODES && Array.isArray(window.RECENT_MODULE_CODES)) 
                    ? window.RECENT_MODULE_CODES 
                    : [];

                if (recentCodes.length > 0) {
                    recentCodes.forEach(function (code) {
                        let cleanCode = code.replace(/\s+/g, '').toUpperCase();
                        let found = modules.find(function (m) {
                            return m[1].replace(/\s+/g, '').toUpperCase() === cleanCode;
                        });
                        if (found && !displayModules.includes(found)) {
                            displayModules.push(found);
                        }
                    });

                    modules.forEach(function (mod) {
                        if (mod[3] === 1 && !displayModules.includes(mod)) {
                            displayModules.push(mod);
                        }
                    });
                } else {
                    displayModules = modules.filter(function (mod) {
                        return mod[3] === 1;
                    });
                }
            } else {
                displayModules = modules.filter(function (mod) {
                    let y = mod[0], code = mod[1], name = mod[2];
                    let matchYear = (selectedYear === "All" || selectedYear == y);
                    let matchSearch = code.toLowerCase().includes(searchText) || name.toLowerCase().includes(searchText);
                    return matchYear && matchSearch;
                });
            }

            let html = "";
            displayModules.forEach(function (mod) {
                let code = mod[1], name = mod[2];

                let viewButtonHtml = (typeof window.IS_LOGGED_IN !== 'undefined' && window.IS_LOGGED_IN)
                    ? `<a href="view-notes.php?code=${encodeURIComponent(code)}&name=${encodeURIComponent(name)}" class="btn btn-sm text-white" style="background-color: #967bb6;">View Notes ▼</a>`
                    : `<button type="button" onclick="showLoginAlert('view')" class="btn btn-sm text-white" style="background-color: #967bb6;">View Notes ▼</button>`;

                html += `
                <div class="col-md-4 mb-3 module-card">
                    <div class="card glass-element glass-card rounded h-100 p-2">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">${code}</h5>
                            <p class="card-text fw-semibold">${name}</p>
                            ${viewButtonHtml}
                        </div>
                    </div>
                </div>`;
            });

            if (displayModules.length === 0) {
                html = `<div class="col-12 text-center text-muted mt-4"><p>No modules found for your search.</p></div>`;
            }

            cardContainer.innerHTML = html;
        }

        renderCards();
        searchInput.addEventListener('input', renderCards);
        yearFilter.addEventListener('change', renderCards);
    }

    let selectElement = document.getElementById("categorySelect");
    if (selectElement) {
        modules.forEach(function (mod) {
            let code = mod[1];
            let name = mod[2];
            let fullSubject = code + " - " + name;

            let option = document.createElement("option");
            option.value = fullSubject;
            option.textContent = fullSubject;

            selectElement.insertBefore(option, selectElement.lastElementChild);
        });
    }
});