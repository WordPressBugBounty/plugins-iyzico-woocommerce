# Dil dosyalarının yolu
LANG_DIR = i18n/languages

# Spesifik .po dosyaları
PO_FILES = $(LANG_DIR)/woocommerce-iyzico-en_US.po \
           $(LANG_DIR)/woocommerce-iyzico-tr_TR.po

# .mo dosyaları için hedefler
MO_FILES = $(PO_FILES:.po=.mo)

# Tüm PHP dosyalarının yolu
PHP_FILES = $(shell find . -type f -name "*.php")

# Dil dosyalarını oluştur
create-lang-files: $(MO_FILES)
	@echo "Dil dosyaları oluşturuldu."

# .mo dosyalarını oluştur
%.mo: %.po
	msgfmt -o $@ $<

# PHPCompatibility testi
phpcompat-test:
	@if phpcs --standard=PHPCompatibility --runtime-set testVersion 7.4-8.1 $(PHP_FILES); then \
		echo "Hata yok. Tüm dosyalar uyumlu."; \
	fi

# Temizleme hedefi
clean-lang-files:
	rm -f $(MO_FILES)
	@echo "Dil dosyaları temizlendi."

# .PHONY tanımlamaları
.PHONY: clean-lang-files create-lang-files phpcompat-test help

# Yardım mesajı
help:
	@echo "Kullanılabilir komutlar:"
	@echo "  make create-lang-files      - Dil dosyalarını .mo formatına çevirir"
	@echo "  make clean-lang-files       - Oluşturulan .mo dosyalarını siler"
	@echo "  make phpcompat-test         - PHPCompatibility testlerini çalıştırır"
	@echo "  make help                   - Bu yardım mesajını gösterir"
