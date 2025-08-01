jQuery(document).ready(function($) {
    const $chatWindow = $('#chatbot-window');
    const $messages = $('.chatbot-messages');
    const $input = $('#chatbot-content');
    let productCategories = [];
    let blogCategories = [];

    // Préchargement des catégories produits
    $.post(chatbotData.ajax_url, { action: 'get_product_categories' }, function(response) {
        if (Array.isArray(response)) {
            productCategories = response;
        }
    });

    // Préchargement des catégories blog
    $.post(chatbotData.ajax_url, { action: 'get_blog_categories' }, function(response) {
        if (Array.isArray(response)) {
            blogCategories = response;
        }
    });


    function resetChat() {
        $('#chatbot-avatar').attr('src', chatbotData.bot_avatar);
        $('#chatbot-name').text(chatbotData.bot_name);
        $messages.empty();
        addBotMessage("Bonjour 👋, que puis-je faire pour vous aujourd’hui ?");
        showMainOptions();
    }

    function addBotMessage(text) {
        $messages.append(`
            <div style="display: flex; justify-content: flex-start; margin: 5px 0;">
                <div style="background: #f1f1f1; color: #333; padding: 8px 12px; border-radius: 12px 12px 12px 12px; font-size: 13px; max-width: 75%;">
                    ${text}
                </div>
            </div>
        `);
    }

    function addUserMessage(text) {
        $messages.append(`
            <div style="display: flex; justify-content: flex-end; margin: 5px 0;">
                <div style="background: #0073aa; color: white; padding: 8px 12px; border-radius: 12px 12px 12px 12px; font-size: 13px; max-width: 75%;">
                    ${text}
                </div>
            </div>
        `);
        scrollToBottom();
    }


    function addBotButtons(html) {
        $messages.append(`
            <div style="display: flex; justify-content: flex-start; margin: 5px 0;">
                <div style="background: #f1f1f1; padding: 8px 12px; border-radius: 12px 12px 12px 0; font-size: 13px;">
                    ${html}
                </div>
            </div>
        `);
    }


    function scrollToBottom() {
        $messages.scrollTop($messages[0].scrollHeight);
    }

    function showMainOptions() {
        const buttons = `
            <a href="#" class="chat-option" data-choice="conseils">📘 Je voudrais des conseils</a><br>
            <a href="#" class="chat-option" data-choice="ressources">📦 Je cherche des ressources</a><br>
            <a href="#" class="chat-option" data-choice="lucky">🎁 Je voudrais un cadeau !</a><br>
            <a href="#" class="chat-option" data-choice="documentation">📚 Comment ça fonctionne ?</a><br>
            <a href="#" class="chat-option" data-choice="aide">❓ J'ai besoin d'aide.</a><br>
        `;
        addBotButtons(buttons);
        $input.empty();
    }

    $('#chatbot-button').on('click', function(e) {
        e.preventDefault();
        $chatWindow.toggle();
        if ($chatWindow.is(':visible')) resetChat();
    });

    $('#chatbot-restart').on('click', function(e) {
        e.preventDefault();
        resetChat();
    });

    $chatWindow.on('click', '.chat-option', function(e) {
        e.preventDefault(); // important !
        const choice = $(this).data('choice');
        addUserMessage($(this).text());

        if (choice === 'conseils') {
            if (!blogCategories || blogCategories.length === 0) {
                addBotMessage("Désolé, aucune catégorie d’article n’est disponible pour le moment.");
            } else {
                let html = '';
                const maxToShow = 5;
                const firstCategories = blogCategories.slice(0, maxToShow);
                firstCategories.forEach(cat => {
                    html += `<a class="category-button" data-type="blog" data-id="${cat.id}">${cat.name}</a><br>`;
                });
                addBotButtons("Voici les catégories d'articles les plus populaires aujourd'hui :<br>");
                const $catMsg = $(`
                    <div style="background: #f1f1f1; padding: 8px 12px; border-radius: 12px 12px 12px 0; font-size: 13px;">
                        ${html}
                        ${blogCategories.length > maxToShow ? `<a href="#" class="see-more-categories">Voir plus de catégories...</a>` : ''}
                    </div>
                `);
                $messages.append($catMsg);
                addBotButtons("Vous pouvez aussi voir plus de catégories d'articles dans notre blog.");

                if (blogCategories.length > maxToShow) {
                    $catMsg.on('click', '.see-more-categories', function(e) {
                        e.preventDefault();
                        let remainingHtml = '';
                        const remainingCategories = blogCategories.slice(maxToShow);
                        remainingCategories.forEach(cat => {
                            remainingHtml += `<a class="category-button" data-type="blog" data-id="${cat.id}">${cat.name}</a><br>`;
                        });
                        $(this).before(remainingHtml);
                        $(this).remove();
                    });
                }
            }

        } else if (choice === 'ressources') {
            if (!productCategories || productCategories.length === 0) {
                addBotMessage("Désolé, aucune catégorie de produit n’est disponible pour le moment.");
            } else {
                let html = '';
                const maxToShow = 5;
                const firstCategories = productCategories.slice(0, maxToShow);
                firstCategories.forEach(cat => {
                    html += `<a class="category-button" data-type="product" data-id="${cat.id}">${cat.name}</a><br>`;
                });
                addBotButtons("Voici les catégories de produits les plus populaires aujourd'hui :<br>");
                const $catMsg = $(`
                    <div style="background: #f1f1f1; padding: 8px 12px; border-radius: 12px 12px 12px 0; font-size: 13px;">
                        ${html}
                        ${productCategories.length > maxToShow ? `<a href="#" class="see-more-product-categories">Voir plus de catégories...</a>` : ''}
                    </div>
                `);
                $messages.append($catMsg);
                addBotButtons("Vous pouvez aussi voir plus de catégories de produits dans notre boutique.");

                if (productCategories.length > maxToShow) {
                    $catMsg.on('click', '.see-more-product-categories', function(e) {
                        e.preventDefault();
                        let remainingHtml = '';
                        const remainingCategories = productCategories.slice(maxToShow);
                        remainingCategories.forEach(cat => {
                            remainingHtml += `<a class="category-button" data-type="product" data-id="${cat.id}">${cat.name}</a><br>`;
                        });
                        $(this).before(remainingHtml);
                        $(this).remove();
                    });
                }
            }


        } else if (choice === 'aide') {
            addBotMessage(`Pour des réponses à vos questions, je vous conseille de consulter la FAQ : <a href="${chatbotData.faq_url}" target="_blank">${chatbotData.faq_url}</a>`);
            showSatisfactionOptions();
        } else if (choice === 'documentation') {
            const pdfUrl = chatbotData.doc_url;
            addBotMessage(`Voici la documentation complète : <a href="${pdfUrl}" target="_blank">📄 Ouvrir la documentation PDF</a>`);
            showSatisfactionOptions();
        } else if (choice === 'lucky') {

            async function get_random_gratuit_product() {
                try {
                    const response = await $.post(chatbotData.ajax_url, {
                        action: 'get_random_freebie_product'
                    });

                    if (response.success && response.data) {
                        return response.data;
                    } else {
                        console.warn("Aucun produit 'freebie' trouvé.");
                        return null;
                    }
                } catch (error) {
                    console.error("Erreur AJAX pour récupérer un produit gratuit :", error);
                    return null;
                }
            }

            async function showFreebieProduct() {
                addBotMessage("Vous avez choisi de recevoir un cadeau, le voici ! 🎁");

                const r_article = await get_random_gratuit_product();

                if (r_article) {
                    addBotMessage(`<a href="${r_article.link}" target="_blank">${r_article.title}</a>`);
                } else {
                    addBotMessage("Désolé, aucun cadeau disponible pour le moment.");
                }

                showSatisfactionOptions();
            }

            showFreebieProduct();
        }


    });

    $chatWindow.on('click', '.category-button', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const type = $(this).data('type');
        const action = type === 'blog' ? 'get_posts_by_category' : 'get_products_by_category';
        const name = type === 'blog' ? 'Montre moi les articles concernant : ' + $(this).text() : 'Donne moi les produits associés à : ' + $(this).text();
        addUserMessage(name);


        $.post(chatbotData.ajax_url, { action, category_id: id }, function(response) {
            if (response.length === 0 || response === 'null') {
                addBotMessage("Désolé mais je n'ai trouvé aucun contenu dans cette catégorie.");
            } else {
                if (response.length === 1) {
                    addBotMessage("Voici l'article que j’ai trouvé dans cette catégorie :");
                }
                else if (response.length > 1) {
                    addBotMessage(`J'ai trouvé ${response.length} articles pour vous :`);
                }

                let html = '';
                response.forEach(item => {
                    html += `<div style="margin:5px 0;"><a href="${item.link}" target="_blank">${item.title}</a></div>`;
                });
                addBotButtons(html);
            }
            showSatisfactionOptions();
        });
    });

    function showSatisfactionOptions() {
        const html = `
            <p style="margin-bottom:5px;">Êtes-vous satisfait ?</p>
            <a class="satisfaction" data-answer="yes">✅ Oui, j'ai trouvé ce qu'il me faut</a><br>
            <a class="satisfaction" data-answer="no">❌ Non, je souhaite contacter le support.</a><br>
        `;
        addBotButtons(html);
        $input.empty();
    }

    $chatWindow.on('click', '.satisfaction', function() {
        const answer = $(this).data('answer');
        addUserMessage(answer === 'yes' ? "Oui, j'ai trouvé ce qu'il me faut." : "Non, je souhaite contacter le support.");

        if (answer === 'yes') {
            addBotMessage("Super ! N'hésitez pas à revenir si besoin.");
            setTimeout(() => {
                resetChat();
            }, 1000);
        } else {
            addBotMessage(`Vous pouvez contacter notre support à cette adresse : <a href="mailto:${chatbotData.support_email}">${chatbotData.support_email}</a>`);
        }
    });

    $chatWindow.on('click', '.restart', function() {
        addUserMessage("Recommencer 🔁");
        resetChat();
    });


    resetChat();
});
