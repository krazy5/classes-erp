import "./bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const selects = document.querySelectorAll('select[data-behavior="student-search"]');

    selects.forEach((select) => {
        if (select.dataset.studentSearchEnhanced) {
            return;
        }

        const searchInput = document.createElement("input");
        searchInput.type = "search";
        searchInput.placeholder = select.dataset.searchPlaceholder || "Search students...";
        searchInput.className = "mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100";

        if (!select.parentElement) {
            return;
        }

        select.parentElement.insertBefore(searchInput, select);

        const originalOptions = Array.from(select.options).map((option) => ({
            value: option.value,
            text: option.text,
            selected: option.selected,
        }));

        const rebuildOptions = (term) => {
            const lowerTerm = term.trim().toLowerCase();
            const currentValue = select.value;

            select.innerHTML = "";

            originalOptions.forEach((optionData) => {
                const matches = optionData.text.toLowerCase().includes(lowerTerm) || optionData.value === "";
                const shouldInclude = lowerTerm === "" || matches || optionData.value === currentValue;

                if (!shouldInclude) {
                    return;
                }

                const option = document.createElement("option");
                option.value = optionData.value;
                option.textContent = optionData.text;
                option.selected = optionData.value === currentValue;
                select.appendChild(option);
            });
        };

        searchInput.addEventListener("input", () => rebuildOptions(searchInput.value));
        rebuildOptions("");

        select.dataset.studentSearchEnhanced = "true";
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const shareButtons = document.querySelectorAll("[data-share]");

    shareButtons.forEach((button) => {
        if (button.dataset.shareEnhanced) {
            return;
        }

        button.addEventListener("click", async () => {
            const payload = button.getAttribute("data-share") || "";
            const title = button.getAttribute("data-share-title") || "Update";

            if (navigator.share) {
                try {
                    await navigator.share({ title, text: payload });
                    return;
                } catch (error) {
                    if (error.name === "AbortError") {
                        return;
                    }
                }
            }

            try {
                await navigator.clipboard.writeText(payload);
                button.classList.add("border-emerald-300", "text-emerald-600");
                setTimeout(() => {
                    button.classList.remove("border-emerald-300", "text-emerald-600");
                }, 2000);
            } catch (clipboardError) {
                alert("Sharing is not supported on this device. The details could not be copied automatically.");
            }
        });

        button.dataset.shareEnhanced = "true";
    });
});
