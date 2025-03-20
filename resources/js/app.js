console.log('vConsole is worked!');

import { createAppKit } from '@reown/appkit';
import { mainnet } from '@reown/appkit/networks';
import { EthersAdapter } from '@reown/appkit-adapter-ethers';
import { BrowserProvider, ethers } from 'ethers';

// 1. Укажите ваш Project ID, полученный из Reown Cloud
const projectId = '4245768f884327f17fabceb32be2260d';

// 2. Определите сеть (Ethereum Mainnet)
const networks = [mainnet];

// 3. Настроим адаптер для Ethers.js
const ethersAdapter = new EthersAdapter({
  projectId,
  networks,
  ethers, // Передаём Ethers.js
});

// 4. Укажите метаданные приложения
const metadata = {
  name: 'HORNIVERSE DApp',
  description: 'HORNIVERSE DApp',
  url: 'https://uni2.ekaterinabeska.com/',
  icons: ['https://uni2.ekaterinabeska.com/icon.png'],
};

console.log("Инициализация AppKit...");

// 5. Создаём экземпляр AppKit
const appKit = createAppKit({
  adapters: [ethersAdapter],
  networks,
  metadata,
  projectId,
});

console.log("appKit создан:", appKit);

// 6. Функция для получения провайдера
async function getWalletProvider() {
  console.log("🔍 Получаем доступные коннекторы...");
  const connectors = appKit.getConnectors();
  console.log("📡 Доступные коннекторы:", connectors);

  let provider = null;
  for (const connector of connectors) {
    if (connector.provider) {
      provider = connector.provider;
      console.log("✅ Найден работающий провайдер:", connector.name);
      break;
    }
  }

  if (!provider) {
    throw new Error("❌ Не удалось найти активный Web3-провайдер.");
  }

  return new BrowserProvider(provider);
}

// 7. Обработчик кнопки "Login"
document.getElementById("login").addEventListener("click", async () => {
  console.log("📢 Кнопка нажата, открываем WalletConnect...");
  await appKit.open(); // Открываем модальное окно для подключения

  let attempts = 0;
  const maxAttempts = 10; // Лимит проверок

  const intervalId = setInterval(async () => {
    console.log(`🔍 Попытка ${attempts + 1}: проверяем подключение...`);
    const address = await appKit.getAddress();

    if (address) {
      console.log("✅ Кошелёк подключён:", address);

      try {
        const ethersProvider = await getWalletProvider();
        console.log("ethersProvider:", ethersProvider);

        const signer = await ethersProvider.getSigner();
        console.log("signer:", signer);

        const message = `Sign in to HORNIVERSE with address: ${address}`;
        console.log("Сообщение для подписи:", message);

        const signature = await signer.signMessage(message);
        console.log("🔏 Подпись:", signature);

        // Отправка данных на сервер
        fetch("/walletconnect-login/auth", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ address, signature }),
        })
        .then(response => response.json())
        .then(data => {
          console.log("📡 Ответ сервера:", data);
          clearInterval(intervalId); // Остановить таймер после успешного запроса
        })
        .catch(error => console.error("❌ Ошибка запроса:", error));

      } catch (error) {
        console.error("❌ Ошибка:", error);
      }

    } else {
      console.log("❌ Кошелёк НЕ подключён");
    }

    attempts++;
    if (attempts >= maxAttempts) {
      console.log("⚠️ Превышено максимальное количество попыток подключения.");
      clearInterval(intervalId);
    }

  }, 3000);
});