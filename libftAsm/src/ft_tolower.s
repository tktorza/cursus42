section .text
global _ft_tolower
_ft_tolower:
	mov rax, rdi
	cmp rdi, 64
	jg toup
	ret

toup:
	cmp rdi, 91
	jl ok
	ret
ok:
	add rdi, 32
	mov rax, rdi
	ret