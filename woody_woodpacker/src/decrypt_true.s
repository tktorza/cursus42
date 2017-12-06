global _ft_decrypt

section .text
_ft_decrypt:
	push rbp
	mov rbp, rsp
	mov rdi, 0x22222222222 ; data[k]
	mov rax, 0x33333333333 ; index
	mov rsi, 0x44444444444 ; size
	mov rcx, 0x55555555555 ; k
	mov rbx, rcx		   ; k_start
	mov r8, 0x66666666666 ; key	
	mov rdx, 1 ; sign
	;revoir la division

loop:
	cmp rcx, rsi ;si arrive fin de .text
	je return
	;compare with mask
	
	push rbx ;remettre
	push rsi ;remettre
	
	mov rbx, rdi 	;rbx = data[k] --> x 
	mov rsi, rdi 	;rsi = data[k] --> y

	cmp rdx, 0
	jg more
	
	push rdx
	and rbx, rax 	;x = data[k] & index
	push rax		; on sauvegarde l'index
	push rbx
	mov rbx, 2
	div rbx 		;rbx = index-- (on se deplace d'un bit)
	and rsi, rbx	;rbx(y) = data[k] & index--
	pop rbx
	pop rax	
	sub rdi, rbx	;data[k] - x
	sub rdi, rsi	;data[k] - y
	shr rbx			;x >> 
	sal rsi			;y <<
	add rdi, rbx	;data[k] + x
	add rdi, rsi	;data[k] + y
	pop rdx
	and rsi, rax
	pop rsi
	pop rbx
	inc rcx ;increment de k fin de loop
	mov rdi, [rdi + k] ;increment de rdi

index:
	push rcx
	sub rcx, rbx
	push rdx ; on stocke le signe
	push rax ; stocke l'index
	div rcx, r8
	cmp rdx, 0
	je key_index
	pop rax
	pop rdx

indexi:
	cmp rdx, 0
	ja pos
	cmp rax, 2
	jg moins
	mov rdx, 1
	jmp loop

key_index:
	;pop rdx
	mov rax, r8
	div rax, 7
	mov rax, rdx
	pop rdx
	jmp loop

more:
	and rbx, rax 	;x = data[k] & index
	imul rbx, 2 		;rbx = index++ (on se deplace d'un bit)
	and rsi, rbx	;rbx(y) = data[k] & index++
	sub rdi, rbx	;data[k] - x
	sub rdi, rsi	;data[k] - y
	sal rbx			;x << 
	shr rsi			;y >>
	add rdi, rbx	;data[k] + x
	add rdi, rsi	;data[k] + y
	pop rsi
	pop rbx
	inc rcx ;increment de k fin de loop
	mov rdi, [rdi + k] ;increment de rdi
	jmp index

plus:
	imul rax, 2

moins:
;a revoir
	div rax, 2

pos:
	cmp rax, 64
	jl plus
	mov rdx, -1
	jmp loop
